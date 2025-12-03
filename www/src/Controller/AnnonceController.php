<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Room;
use App\Entity\User;
use App\Entity\User_Room;
use App\Entity\Role; //
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use App\Repository\RoleRepository; //
use App\Service\FileUploadService;
use JulienLinard\Auth\AuthManager;
use JulienLinard\Auth\Middleware\AuthMiddleware;
use JulienLinard\Auth\Middleware\RoleMiddleware;
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

    #[Route(path: '/mesAnnonces', name: 'mesAnnonces', methods: ['GET'], middleware: [AuthMiddleware::class, new RoleMiddleware('hote', '/')])]
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

    #[Route(path: "/room/create", name: "app_room_create_form", methods: ["GET"], middleware: [AuthMiddleware::class])]
    public function createForm(): Response
    {
        return $this->view('Annonces/createRoom', [
            'title' => 'Créer une annonce',
            'errors' => [],
            'old' => ['title' => '', 'description' => '', 'country' => '', 'city' => '', 'price_per_night' => '', 'number_of_bed' => '']
        ]);
    }

    #[Route(path: "/room/create", name: "app_add_room", methods: ["POST"], middleware: [AuthMiddleware::class])]
    public function create(Request $request): Response
    {
        $user = $this->auth->user();
        if (!$user) {
            return $this->redirect('/login');
        }

        // 1. Récupération des données du formulaire
        $title = trim($request->getPost('title', '') ?? '');
        $description = trim($request->getPost('description', '') ?? '');
        $country = trim($request->getPost('country', '') ?? '');
        $city = trim($request->getPost('city', '') ?? '');
        $price_per_night = (int)$request->getPost('price_per_night', 0);
        $number_of_bed = (int)$request->getPost('number_of_bed', 0);

        // 2. Validation des champs obligatoires
        $errors = [];
        if (empty($title)) $errors['title'] = 'Le titre est requis';
        if (empty($country)) $errors['country'] = 'Le pays est requis';
        if (empty($city)) $errors['city'] = 'La ville est requise';
        if ($price_per_night <= 0) $errors['price_per_night'] = 'Le prix doit être positif';
        if ($number_of_bed <= 0) $errors['number_of_bed'] = 'Le nombre de lits est requis';

        // 3. Gestion et validation de l'image
        $imagePath = null;
        if (isset($_FILES['media']) && $_FILES['media']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = $this->fileUploadService->upload($_FILES['media']);
            if ($uploadResult->isSuccess()) {
                $imagePath = ltrim($uploadResult->getData()['path'], '/');
            } else {
                // Ajout de l'erreur d'upload à la liste des erreurs
                $errors['general'] = $uploadResult->getError() ?? 'Erreur de chargement de l\'image.';
            }
        }

        // 4. S'il y a des erreurs, on réaffiche le formulaire avec les anciennes données
        if (!empty($errors)) {
            Session::flash('error', 'Veuillez corriger les erreurs.');
            return $this->view('Annonces/createRoom', [
                'title' => 'Créer une annonce',
                'errors' => $errors,
                'old' => [
                    'title' => $title,
                    'description' => $description,
                    'country' => $country,
                    'city' => $city,
                    'price_per_night' => $price_per_night,
                    'number_of_bed' => $number_of_bed
                ]
            ]);
        }

        // --- Si aucune erreur de validation, on procède à la sauvegarde ---
        try {
            // 1. Création et sauvegarde de la Room (pour obtenir l'ID)
            $room = new Room();
            $room->title = $title;
            $room->description = $description;
            $room->country = $country;
            $room->city = $city;
            $room->price_per_night = $price_per_night;
            $room->number_of_bed = $number_of_bed;
            $room->created_at = new \DateTime();
            $room->updated_at = new \DateTime();
            $room->is_reserved = false;
            $room->media_path = $imagePath;

            $this->em->persist($room);
            $this->em->flush(); // FLUSH 1 : Indispensable pour l'ID Room

            // 2. Création de la liaison User_Room
            $userEntity = $this->em->createRepository(UserRepository::class, User::class)->find($user->id);
            $this->em->persist($userEntity);

            $userRoom = new User_Room();
            $userRoom->user = $userEntity;
            $userRoom->room = $room;
            $this->em->persist($userRoom);

            // 3. Changement de rôle (Si pas déjà Hôte)
            // Vérification de la propriété string
            if ($userEntity->role !== 'hote') {

                $userEntity->role = 'hote'; // Mise à jour directe de la propriété string

                // Force le statut 'dirty'
                $userEntity->updated_at = new \DateTime();

                // Note: $this->em->persist($userEntity) déjà appelé au début du bloc
            }

            // 4. FLUSH FINAL (Enregistre User_Room et l'UPDATE User)
            $this->em->flush();

            // 5. MISE A JOUR DE LA SESSION
            if (method_exists($this->auth, 'login')) {
                $this->auth->login($userEntity);
            }

            if ($userEntity->role !== 'hote') {
                Session::flash('success', 'Annonce créée ! Vous êtes maintenant Hôte.');
            } else {
                Session::flash('success', 'Annonce créée !');
            }

            return $this->redirect('/mesAnnonces');

        } catch (\Exception $e) {
            Session::flash('error', 'Erreur système : ' . $e->getMessage());
            return $this->redirect('/room/create');
        }
    }

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

        $roomRepo = $this->em->createRepository(RoomRepository::class, Room::class);
        $room = $roomRepo->find($id);

        if (!$room) {
            Session::flash('error', 'Annonce introuvable.');
            return $this->redirect('/mesAnnonces');
        }

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

        if ($room->id !== $user->id) {
            Session::flash('error', 'Vous n’êtes pas autorisé à modifier cette annonce.');
            return $this->redirect('/mesAnnonces');
        }

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
            $room->title = $title;
            $room->description = $description;
            $room->country = $country;
            $room->city = $city;
            $room->price_per_night = $price_per_night;
            $room->number_of_bed = $number_of_bed;
            $room->updated_at = new \DateTime();

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

            $this->em->persist($room);
            $this->em->flush();

            Session::flash('success', 'Annonce modifiée avec succès !');
            return $this->redirect('/mesAnnonces');

        } catch (\Exception $e) {
            Session::flash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            return $this->redirect('/room/edit?id=' . $id);
        }
    }

    #[Route(path: "/room/{id}", name: "app_room_show", methods: ["GET"])]
    public function show(Request $request): Response
    {
        // 1. Récupérer l'ID de la Room depuis l'URL (le chemin)
        $id = (int) $request->getRouteParam('id', 0);

        if ($id === 0) {
            // Optionnel : rediriger ou afficher une erreur si l'ID est invalide
            return $this->redirect('/');
        }

        // 2. Charger l'annonce depuis la base de données
        $roomRepo = $this->em->createRepository(RoomRepository::class, Room::class);
        $room = $roomRepo->find($id);

        if (!$room) {
            // Annonce introuvable : rediriger ou afficher une erreur 404
            return $this->redirect('/');
        }

        // 3. Rendre la vue de l'annonce
        return $this->view('Annonces/showRoom', [
            'room' => $room,
            'title' => $room->title . ' - Airbnb'
        ]);
    }

    #[Route(path: "/room/{id}/delete", name: "app_room_delete", methods: ["Post"], middleware: [AuthMiddleware::class])]
    public function delete(Request $request): Response
    {
        $id = (int) $request->getPost('id', 0);
        if ($id === 0) {
            Session::flash('error', 'Annonce introuvable.');
            return $this->redirect('/');
        }
        $roomRepo = $this->em->createRepository(RoomRepository::class, Room::class);
        $room = $roomRepo->find($id);
        if (!$room) {
            Session::flash('error', 'Annonce introuvable.');
            return $this->redirect('/');
        }

        try{
            $this->em->remove($room);
            $this->em->flush();
            Session::flash('success', 'Todo supprimé avec succès !');
            return $this->redirect('/mesAnnonces');
        } catch (\Exception $e) {
            Session::flash('error', 'Une erreur est survenue lors de la suppression du todo');
            return $this->redirect('/mesAnnonces');
        }

    }
}