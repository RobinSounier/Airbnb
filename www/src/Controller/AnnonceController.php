<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Room;
use App\Entity\User;
use App\Entity\User_Room;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
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

        // 2. Validation (Votre logique existante)
        $errors = [];
        if (empty($title)) $errors['title'] = 'Le titre est requis';
        if (empty($country)) $errors['country'] = 'Le pays est requis';
        if (empty($city)) $errors['city'] = 'La ville est requise';
        if ($price_per_night <= 0) $errors['price_per_night'] = 'Le prix doit être positif';
        if ($number_of_bed <= 0) $errors['number_of_bed'] = 'Le nombre de lits est requis';

        $imagePath = null;

        // On vérifie si un fichier 'media' a été envoyé sans erreur
        if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {

            $file = $_FILES['media'];
            $fileName = $file['name'];
            $tmpName  = $file['tmp_name'];
            $size     = $file['size'];

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // 1. Vérifications
            if (!in_array($extension, $allowedExtensions)) {
                $errors['general'] = "Format d'image non supporté.";
            }
            elseif ($size > 10 * 1024 * 1024) { // 10 Mo
                $errors['general'] = "L'image est trop lourde (max 10Mo).";
            }
            else {
                // 2. Upload
                $uploadDir = __DIR__ . '/../../public/uploads/rooms/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $newFilename = uniqid('room_', true) . '.' . $extension;

                if (move_uploaded_file($tmpName, $uploadDir . $newFilename)) {
                    $imagePath = 'uploads/rooms/' . $newFilename;
                } else {
                    $errors['general'] = "Erreur lors de la sauvegarde de l'image.";
                }
            }
        }


        // S'il y a des erreurs, on réaffiche le formulaire
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

        try {
            // Création de la Room
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

            // Assignation de l'image
            $room->media_path = $imagePath; // Sera null si pas d'image ou erreur

            $this->em->persist($room);
            $this->em->flush(); // Nécessaire pour avoir l'ID de la room

            // Création de la liaison User_Room
            $userEntity = $this->em->createRepository(UserRepository::class, User::class)->find($user->id);

            $userRoom = new User_Room();
            $userRoom->user = $userEntity;
            $userRoom->room = $room;

            $this->em->persist($userRoom);
            $this->em->flush();

            Session::flash('success', 'Le bien a été ajouté avec succès !');
            return $this->redirect('/mesAnnonces');

        } catch (\Exception $e) {
            Session::flash('error', 'Erreur système : ' . $e->getMessage());
            return $this->redirect('/room/create');
        }
    }

// --- AJOUTER DANS AnnonceController ---

    #[Route(path: "/room/edit", name: "app_edit_room_form", methods: ["GET"], middleware: [AuthMiddleware::class])]
    public function editForm(Request $request): Response
    {
        $user = $this->auth->user();
        if (!$user) {
            return $this->redirect('/login');
        }

        // 1. Récupérer ID
        $roomRepo = $this->em->createRepository(RoomRepository::class, Room::class);

// 1. Récupérer l’ID dans l’URL
        $id = (int) $request->getQueryParam('id', 0);

// Débogage si besoin


    if ($id != $user->id) {
        $isOwner = false;
    } else {
        $isOwner = true;
    }

        if ($id === 0) {
            Session::flash('error', 'ID invalide.');
            return $this->redirect('/mesAnnonces');
        }

        if ($id === 0) {
            Session::flash('error', 'Annonce introuvable.');
            return $this->redirect('/mesAnnonces');
        }

        // 2. Charger l'annonce
        $roomRepo = $this->em->createRepository(RoomRepository::class, Room::class);
        $room = $roomRepo->find($id);

        if (!$room) {
            Session::flash('error', 'Annonce introuvable.');
            return $this->redirect('/mesAnnonces');
        }

        // 3. Vérifier propriétaire

        if (!$isOwner) {
            Session::flash('error', 'Accès refusé.');
            return $this->redirect('/mesAnnonces');
        }

        // 4. Rendre la vue
        return $this->view('Annonces/editRoom', [
            'room' => $room,
            'errors' => []
        ]);
    }


    #[Route(path: "/room/edit", name: "app_edit_room_process", methods: ["POST"], middleware: [AuthMiddleware::class])]
    public function update(Request $request): Response
    {
        // 1. Vérifier que l'utilisateur est connecté
        $user = $this->auth->user();
        if (!$user) {
            return $this->redirect('/login');
        }

        // 2. Récupérer l'ID de l'annonce
        $id = (int) $request->getPost('id', 0);
        $roomRepo = $this->em->createRepository(RoomRepository::class, Room::class);
        /** @var Room|null $room */
        $room = $roomRepo->find($id);

        // 3. Vérifier que l'annonce existe
        if (!$room) {
            Session::flash('error', 'Annonce introuvable.');
            return $this->redirect('/mesAnnonces');
        }

        // (Optionnel mais recommandé) Vérifier que l'annonce appartient bien à l'utilisateur
        // ... votre logique de vérification ici ...

        // 4. Récupérer les nouvelles données du formulaire
        $title = trim($request->getPost('title', '') ?? '');
        $description = trim($request->getPost('description', '') ?? '');
        $country = trim($request->getPost('country', '') ?? '');
        $city = trim($request->getPost('city', '') ?? '');
        $price_per_night = (int)$request->getPost('price_per_night', 0);
        $number_of_bed = (int)$request->getPost('number_of_bed', 0);

        // Validation simple
        if (empty($title) || $price_per_night <= 0) {
            Session::flash('error', 'Titre et prix sont obligatoires.');
            return $this->redirect('/room/edit?id=' . $id);
        }

        try {
            // 5. Mise à jour des informations texte
            $room->title = $title;
            $room->description = $description;
            $room->country = $country;
            $room->city = $city;
            $room->price_per_night = $price_per_night;
            $room->number_of_bed = $number_of_bed;
            $room->updated_at = new \DateTime();

            // 6. GESTION INTELLIGENTE DE L'IMAGE
            // On ne fait quelque chose que si un nouveau fichier est envoyé
            if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {

                $file = $_FILES['media'];
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                // Validation du format
                if (in_array($extension, $allowedExtensions)) {
                    $uploadDir = __DIR__ . '/../../public/uploads/rooms/';

                    // Créer le dossier s'il n'existe pas
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    // Générer un nom unique
                    $newFilename = uniqid('room_', true) . '.' . $extension;

                    // Tenter l'upload
                    if (move_uploaded_file($file['tmp_name'], $uploadDir . $newFilename)) {

                        // === SUPPRESSION DE L'ANCIENNE IMAGE ===
                        // C'est ici qu'on nettoie le serveur
                        if ($room->media_path) {
                            $oldFilePath = __DIR__ . '/../../public/' . $room->media_path;
                            if (file_exists($oldFilePath) && is_file($oldFilePath)) {
                                @unlink($oldFilePath); // @ pour éviter une erreur si le fichier est déjà parti
                            }
                        }

                        // Mise à jour du chemin en base de données
                        $room->media_path = 'uploads/rooms/' . $newFilename;
                    }
                } else {
                    Session::flash('error', 'Format d\'image non supporté.');
                    return $this->redirect('/room/edit?id=' . $id);
                }
            }

            // 7. Sauvegarde finale
            // Pas besoin de persist($room) car l'objet est déjà suivi ("managed") par l'EntityManager
            $this->em->persist($room);
            $this->em->flush();

            Session::flash('success', 'Annonce modifiée avec succès !');
            return $this->redirect('/mesAnnonces');

        } catch (\Exception $e) {
            Session::flash('error', 'Erreur lors de la modification : ' . $e->getMessage());
            return $this->redirect('/room/edit?id=' . $id);
        }
    }



}
