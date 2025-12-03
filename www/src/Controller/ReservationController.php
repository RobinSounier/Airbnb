<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Room;
use App\Entity\User;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use JulienLinard\Auth\AuthManager;
use JulienLinard\Auth\Middleware\AuthMiddleware;
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
        // SUPPRIMÉ : private ReservationRepository $reservationRepository,
    ) {}

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

        // Validation des dates
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
            return $this->redirect('/reservation/success');

        } catch (\Exception $e) {
            Session::flash('error', 'Erreur système lors de la réservation : ' . $e->getMessage());
            return $this->redirect('/reservation/create?room_id=' . $roomId);
        }
    }


    #[Route(path: "/reservation/success", name: "app_reservation_success", methods: ["GET"], middleware: [AuthMiddleware::class])]
    public function success(): Response
    {
        if (!Session::has('success')) {
            return $this->redirect('/');
        }

        return $this->view('Reservation/reservationSuccess', [
            'title' => 'Réservation Confirmée',
        ]);
    }
}