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

        // Récupération des champs classiques
        $title = trim($request->getPost('title', '') ?? '');
        $description = trim($request->getPost('description', '') ?? '');
        $country = trim($request->getPost('country', '') ?? '');
        $city = trim($request->getPost('city', '') ?? '');
        $price_per_night = (int)$request->getPost('price_per_night', 0);
        $number_of_bed = (int)$request->getPost('number_of_bed', 0);

        // --- VALIDATION DES CHAMPS ---
        $errors = [];
        if (empty($title)) $errors['title'] = 'Le titre est requis';
        if (empty($country)) $errors['country'] = 'Le pays est requis';
        if (empty($city)) $errors['city'] = 'La ville est requise';
        if ($price_per_night <= 0) $errors['price_per_night'] = 'Le prix doit être positif';
        if ($number_of_bed <= 0) $errors['number_of_bed'] = 'Le nombre de lits est requis';

        // --- GESTION DE L'IMAGE ---
        $imagePath = null;

        // On vérifie si un fichier a été envoyé dans le tableau 'media' à l'index 0
        if (!empty($_FILES['media']['name'][0])) {
            $tmpName = $_FILES['media']['tmp_name'][0];
            $error = $_FILES['media']['error'][0];
            $fileName = $_FILES['media']['name'][0];
            $size = $_FILES['media']['size'][0];

            if ($error === UPLOAD_ERR_OK) {
                // 1. Validation de l'extension
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (!in_array($extension, $allowedExtensions)) {
                    $errors['general'] = "Format d'image non supporté (jpg, png, webp uniquement).";
                }
                // 2. Validation de la taille (ex: 5Mo)
                elseif ($size > 5 * 1024 * 1024) {
                    $errors['general'] = "L'image est trop lourde (max 5Mo).";
                }
                else {
                    // 3. Upload
                    $uploadDir = __DIR__ . '/../../public/uploads/rooms/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $newFilename = uniqid('room_', true) . '.' . $extension;

                    if (move_uploaded_file($tmpName, $uploadDir . $newFilename)) {
                        // Chemin à stocker en BDD
                        $imagePath = 'uploads/rooms/' . $newFilename;
                    } else {
                        $errors['general'] = "Erreur lors de la sauvegarde de l'image.";
                    }
                }
            }
        }

        // S'il y a des erreurs, on renvoie la vue
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

            // On assigne l'image
            $room->media_path = $imagePath; //

            $this->em->persist($room);
            $this->em->flush(); // Important : Flush ici pour avoir l'ID de la Room

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
            // En production, logguez $e->getMessage()
            Session::flash('error', 'Une erreur est survenue : ' . $e->getMessage());
            return $this->redirect('/room/create');
        }
    }





}
