<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Room;
use App\Entity\User;
use App\Entity\User_Room;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use App\Repository\User_RoomRepository; // Import nécessaire
use App\Service\FileUploadService;
use JulienLinard\Auth\AuthManager;
use JulienLinard\Auth\Middleware\AuthMiddleware;
use JulienLinard\Core\Controller\Controller;
use JulienLinard\Core\Session\Session;
use JulienLinard\Doctrine\EntityManager;
use JulienLinard\Router\Attributes\Route;
use JulienLinard\Router\Request;
use JulienLinard\Router\Response;


class AnnonceController extends Controller
{
    public function __construct(
        private AuthManager $auth,
        private EntityManager $em,
        private FileUploadService $fileUploadService
    ) {}

    #[Route(path: '/mesAnnonces', name: 'mesAnnonces', methods: ['GET'], middleware: [AuthMiddleware::class])]
    public function mesAnnonces(): Response
    {
        $user = $this->auth->user();
        $roomRepo = $this->em->createRepository(RoomRepository::class, Room::class);
        $rooms = $roomRepo->findByUser($user->id);

        return $this->view('Annonces/mesAnnonces', [
            'rooms' => $rooms,
            'title' => 'Mes Annonces - Airbnb',
            "user" => $this->auth->user()
        ]);

    }

    // ... (méthodes createForm et create non modifiées) ...

    #[Route(path: "/room/edit", name: "app_edit_room_form", methods: ["GET"], middleware: [AuthMiddleware::class])]
    public function editForm(Request $request): Response
    {
        $user = $this->auth->user();
        if (!$user) {
            return $this->redirect('/login');
        }

        $id = (int) $request->getQueryParam('id', 0);

        if ($id === 0) {
            Session::flash('error', 'Annonce introuvable.');
            return $this->redirect('/mesAnnonces');
        }

        // 1. Charger l'annonce
        $roomRepo = $this->em->createRepository(RoomRepository::class, Room::class);
        $room = $roomRepo->find($id);

        if (!$room) {
            Session::flash('error', 'Annonce introuvable.');
            return $this->redirect('/mesAnnonces');
        }

        // --- NOUVELLE VÉRIFICATION DE SÉCURITÉ (GET) ---
        $userRoomRepo = $this->em->createRepository(User_RoomRepository::class, User_Room::class);
        $isOwner = $userRoomRepo->findOneBy([
            'room_id' => $room->id,
            'user_id' => $user->id
        ]);

        if (!$isOwner) {
            Session::flash('error', 'Accès refusé. Vous n’êtes pas le propriétaire de cette annonce.');
            return $this->redirect('/mesAnnonces');
        }
        // ---------------------------------------------

        // 2. Rendre la vue
        return $this->view('Annonces/editRoom', [
            'room' => $room,
            'errors' => []
        ]);
    }


    #[Route(path: "/room/edit", name: "app_edit_room_process", methods: ["POST"], middleware: [AuthMiddleware::class])]
    public function update(Request $request): Response
    {
        $user = $this->auth->user();
        if (!$user) {
            return $this->redirect('/login');
        }

        $id = (int) $request->getPost('id', 0);
        $roomRepo = $this->em->createRepository(RoomRepository::class, Room::class);
        /** @var Room|null $room */
        $room = $roomRepo->find($id);

        if (!$room) {
            Session::flash('error', 'Annonce introuvable.');
            return $this->redirect('/mesAnnonces');
        }

        // --- VÉRIFICATION DE SÉCURITÉ (POST) ---
        $userRoomRepo = $this->em->createRepository(User_RoomRepository::class, User_Room::class);
        $isOwner = $userRoomRepo->findOneBy([
            'room_id' => $room->id,
            'user_id' => $user->id
        ]);

        if (!$isOwner) {
            Session::flash('error', 'Vous n’êtes pas autorisé à modifier cette annonce.');
            return $this->redirect('/mesAnnonces');
        }
        // -----------------------------------

        $title = trim($request->getPost('title', '') ?? '');
        $description = trim($request->getPost('description', '') ?? '');
        $country = trim($request->getPost('country', '') ?? '');
        $city = trim($request->getPost('city', '') ?? '');
        $price_per_night = (int)$request->getPost('price_per_night', 0);
        $number_of_bed = (int)$request->getPost('number_of_bed', 0);

        if (empty($title) || $price_per_night <= 0) {
            Session::flash('error', 'Titre et prix sont obligatoires.');
            return $this->redirect('/room/edit?id=' . $id);
        }

        try {
            // 1. Mise à jour des propriétés sur l'objet
            $room->title = $title;
            $room->description = $description;
            $room->country = $country;
            $room->city = $city;
            $room->price_per_night = $price_per_night;
            $room->number_of_bed = $number_of_bed;
            $room->updated_at = new \DateTime();

            // 2. GESTION DE L'IMAGE
            if (isset($_FILES['media']) && $_FILES['media']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = $this->fileUploadService->upload($_FILES['media']);

                if ($uploadResult->isSuccess()) {
                    if ($room->media_path) {
                        $this->fileUploadService->delete($room->media_path);
                    }
                    $data = $uploadResult->getData();
                    $room->media_path = ltrim($data['path'], '/');
                } else {
                    Session::flash('error', $uploadResult->getError());
                    return $this->redirect('/room/edit?id=' . $id);
                }
            }

            // 3. CONTOURNEMENT DE L'ORM AVEC SQL DIRECT (pour assurer l'UPDATE)
            $connection = $this->em->getConnection();

            $sql = "UPDATE rooms SET
                title = :title,
                description = :description,
                country = :country,
                city = :city,
                price_per_night = :price,
                number_of_bed = :beds,
                updated_at = :updated_at,
                media_path = :media_path
                WHERE id = :id";

            $params = [
                'title' => $room->title,
                'description' => $room->description,
                'country' => $room->country,
                'city' => $room->city,
                'price' => $room->price_per_night,
                'beds' => $room->number_of_bed,
                'updated_at' => $room->updated_at->format('Y-m-d H:i:s'),
                'media_path' => $room->media_path,
                'id' => $room->id
            ];

            $connection->execute($sql, $params);

            Session::flash('success', 'Annonce modifiée avec succès !');
            return $this->redirect('/mesAnnonces');

        } catch (\Exception $e) {
            Session::flash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            return $this->redirect('/room/edit?id=' . $id);
        }
    }
}