<?php
declare(strict_types=1);
namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Room;
use App\Entity\User;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use Couchbase\Role;
use JulienLinard\Auth\AuthManager;
use JulienLinard\Auth\Middleware\AuthMiddleware;
use JulienLinard\Auth\Middleware\RoleMiddleware;
use JulienLinard\Core\Controller\Controller;
use JulienLinard\Core\Session\Session;
use JulienLinard\Doctrine\EntityManager;
use JulienLinard\Router\Attributes\Route;
use JulienLinard\Router\Request;
use JulienLinard\Router\Response;

class ReservationController extends Controller
{
    public function __construct(
        private AuthManager $auth,
        private EntityManager $em,
    ){}

    #[Route(path: "/reservation/create", name: "app_reservation_create_form", methods: ["GET"], middleware: [AuthMiddleware::class])]
    public function createForm(Request $request): Response
    {
        $roomId = (int) $request->getQueryParam('room_id', 0);

        if ($roomId === 0) {
            Session::flash('error', 'Aucun bien spécifié pour la réservation.');
            return $this->redirect('/');
        }

        $roomRepo = $this->em->createRepository(RoomRepository::class, Room::class);
        $room = $roomRepo->find($roomId);

        if (!$room) {
            Session::flash('error', 'Le bien spécifié est introuvable.');
            return $this->redirect('/');
        }

        return $this->view('Reservation/createReservation', [
            'title' => 'Réserver : ' . $room->title,
            'room' => $room,
            'errors' => [],
            'old' => ['start_date' => '', 'end_date' => '', 'comment' => '']
        ]);
    }

    #[Route(path: "/reservation/create", name: "app_reservation_process", methods: ["POST"], middleware: [AuthMiddleware::class])]
    public function create(Request $request): Response
    {
        $user = $this->auth->user();
        if (!$user) {
            return $this->redirect('/login');
        }

        $roomId = (int) $request->getPost('room_id', 0);
        $startDateStr = trim($request->getPost('start_date', '') ?? '');
        $endDateStr = trim($request->getPost('end_date', '') ?? '');
        $comment = trim($request->getPost('comment', '') ?? '');

        $errors = [];
        $room = null;

        // Validation du bien (Room)
        $roomRepo = $this->em->createRepository(RoomRepository::class, Room::class);
        $room = $roomRepo->find($roomId);

        if (!$room) {
            $errors['general'] = 'Bien introuvable.';
        }


        try {
            $startDate = new \DateTime($startDateStr);
            $endDate = new \DateTime($endDateStr);

            $now = new \DateTime('today');

            if ($startDate < $now) {
                $errors['start_date'] = 'La date d\'arrivée doit être future.';
            }
            if ($endDate <= $startDate) {
                $errors['end_date'] = 'La date de départ doit être postérieure à la date d\'arrivée.';
            }


            $reservationRepo = $this->em->createRepository(ReservationRepository::class, Reservation::class);

            if (empty($errors) && $reservationRepo->isRoomReserved($roomId, $startDate, $endDate)) {
                $errors['general'] = 'Le bien n\'est pas disponible pour les dates demandées.';
            }

        } catch (\Exception $e) {
            var_dump($e->getMessage());
            $errors['date'] = 'Format de date invalide.';
        }

        // S'il y a des erreurs, on réaffiche le formulaire
        if (!empty($errors)) {
            Session::flash('error', $errors['general'] ?? '');
            return $this->view('Reservation/createReservation', [
                'title' => 'Réserver : ' . ($room->title ?? 'Erreur'),
                'room' => $room,
                'errors' => $errors,
                'old' => ['start_date' => $startDateStr, 'end_date' => $endDateStr, 'comment' => $comment]
            ]);
        }

        try {
            $userRepo = $this->em->createRepository(UserRepository::class, User::class);
            $guestEntity = $userRepo->find($user->id);

            // Création de la Réservation
            $reservation = new Reservation();
            $reservation->start_date = $startDate;
            $reservation->end_date = $endDate;
            $reservation->comment = $comment;
            $reservation->created_at = new \DateTime();
            $reservation->guest = $guestEntity;
            $reservation->room = $room;
            $this->em->persist($reservation);
            $this->em->flush();

            Session::flash('success', 'Réservation effectuée avec succès !');
            return $this->view('Reservation/reservationSuccess', [
                'room' => $room,
                'title' => 'Réservation Confirmée'
            ]);

        } catch (\Exception $e) {
            Session::flash('error', 'Erreur système lors de la réservation : ' . $e->getMessage());
            return $this->redirect('/Reservation/create?room_id=' . $roomId);
        }
    }


    #[Route(path: "/reservation/success", name: "app_reservation_success", methods: ["GET"], middleware: [AuthMiddleware::class])]
    public function success(): Response
    {

        return $this->view('Reservation/reservationSuccess', [
            'title' => 'Réservation Confirmée',
        ]);
    }

    #[Route(path: "/mesReservations", name: "app_host_reservations", methods: ["GET"], middleware: [AuthMiddleware::class])]
    public function hostReservations(): Response
    {
        $user = $this->auth->user();



        $reservationRepo = $this->em->createRepository(ReservationRepository::class, Reservation::class);
        $reservations = $reservationRepo->findHostReservations($this->em, $user);

        return $this->view('Reservation/mesReservation', [
            'title' => 'Réservations de mes biens',
            'reservations' => $reservations,
            'auth' => $this->auth
        ]);
    }

    #[Route(path: '/mes-reservations', name: 'host_reservations', methods: ['GET'], middleware: [AuthMiddleware::class, new RoleMiddleware('hote', '/')])]
    public function mesReservationsRecues(): Response
    {
        $user = $this->auth->user();

        $reservationRepo = $this->em->createRepository(ReservationRepository::class, Reservation::class);
        $reservations = $reservationRepo->findReservationsByHost($user->id);

        if (($user->role ?? 'user') !== 'hote') {
            Session::flash('error', 'Accès refusé. Cette page est réservée aux hôtes.');
            return $this->redirect('/');
        }


        return $this->view('Annonces/mesVoyages', [
            'title' => 'Suivi des réservations',
            'reservations' => $reservations,
            'user' => $user
        ]);
    }

    #[Route(path: "/reservation/{id}/delete", name: "app_reservation_delete", methods: ["POST"], middleware: [AuthMiddleware::class])]
    public function reservationDelete(Request $request):Response
    {
        $user = $this->auth->user();
        $reservationId = (int) $request->getPost('id', 0);

        $reservationRepo = $this->em->createRepository(ReservationRepository::class, Reservation::class);
        $reservations = $reservationRepo->find(id: $reservationId);

        if (!$reservations) {
            Session::flash('error', 'Réservation introuvable.');
            return $this->redirect('/mesReservations');
        }


        try {
            $this->em->remove($reservations);
            $this->em->flush();
            Session::flash('success', 'Réservation supprimée avec succès !');
            return $this->redirect('/mesReservations');
        } catch (\Exception $e) {
            Session::flash('error', 'Une erreur est survenue lors de la suppression de la réservation');
            return $this->redirect('/mesReservations');
        }
    }

}