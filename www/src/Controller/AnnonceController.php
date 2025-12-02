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
        private Validator $validator,
        private FileUploadService $fileUpload
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

        $title = trim($request->getPost('title', '') ?? '');
        $description = trim($request->getPost('description', '') ?? '');
        $country = trim($request->getPost('country', '') ?? '');
        $city = trim($request->getPost('city', '') ?? '');
        $price_per_night = (int)$request->getPost('price_per_night', 0);
        $number_of_bed = (int)$request->getPost('number_of_bed', 0);

        if (empty($title)) {
            $errors['title'] = 'Le titre est requis';
        } elseif (strlen($title) > 255) {
            $errors['title'] = 'Le titre ne doit pas dépasser 255 caractères';
        }

        if (empty($country)) {
            $errors['country'] = 'Le pays est requis';
        } elseif (strlen($country) > 100) {
            $errors['country'] = 'Le pays ne doit pas dépasser 100 caractères';
        }

        if (empty($city)) {
            $errors['city'] = 'La ville est requise';
        } elseif (strlen($city) > 100) {
            $errors['city'] = 'La ville ne doit pas dépasser 100 caractères';
        }


        if ($price_per_night === 0) {
            $errors['price_per_night'] = 'Le prix par nuit est requis';
        } elseif ($price_per_night <= 0) {
            $errors['price_per_night'] = 'Le prix par nuit doit être un nombre positif';
        }

        if ($number_of_bed === 0) {
            $errors['number_of_bed'] = 'Le nombre de lits est requis';
        }

        if (!empty($errors)) {
            Session::flash('error', 'Veuillez corriger les erreurs du formulaire');
            return $this->view('Annonces/mesAnnonces', [
                'title' => 'Créer une annonce',
                'errors' => $errors,
                'old' => [
                    'title' => $title ?? '',
                    'description' => $description ?? '',
                    'country' => $country ?? '',
                    'city' => $city ?? '',
                ]
            ]);
        }

        try {
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

            $this->em->persist($room);
            $this->em->flush();

            $authUser = $this->auth->user();
            // 2️⃣ Récupère l’entité User Doctrine correspondante
            $userEntity = $this->em
                ->createRepository(UserRepository::class, \App\Entity\User::class)
                ->find($authUser->id);

            // 3️⃣ Crée la relation User_Room
            $userRoom = new User_Room();
            $userRoom->user = $userEntity;
            $userRoom->room = $room;

            $this->em->persist($userRoom);
            $this->em->flush();


            $userEntity->userRooms[] = $userRoom;
            $room->userRooms[] = $userRoom;

            $this->em->flush();

            $uploadResult = $this->handleMediaUpload($request);
            $uploadErrors = [];
            if ($uploadResult->hasUploaded()) {
                $uploadedFiles = $uploadResult->getUploaded();

                foreach ($uploadedFiles as $mediaData) {
                    try {
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
                        $room->media_path = $mediaData['filename'];


                        $this->em->persist($room);
                        $this->em->flush();

                    } catch (\Exception $e) {
                        $uploadErrors[] = 'Erreur lors de l\'enregistrement de ' . $mediaData['original_filename'] . ': ' . $e->getMessage();
                        // Supprimer le fichier uploadé si l'enregistrement échoue
                        $this->fileUpload->delete($mediaData['filename']);
                    }
                }


            }

            // Afficher les erreurs d'upload s'il y en a
            if ($uploadResult->hasErrors()) {
                $uploadErrors[] = $uploadResult->getErrorsAsString();
            }

            // Invalider le cache pour forcer le rechargement des données
            $queryCache = $this->em->getQueryCache();
            if ($queryCache !== null) {
                $queryCache->invalidateEntity(Todo::class, $todo->id);
            }

            Session::flash('success', 'Le bien a été ajouté avec succès !');
            return $this->redirect('/mesAnnonces');

        } catch (\Exception $e) {
            Session::flash('error', 'Une erreur est survenue lors de l\'ajout du bien.');
            return $this->view('Annonces/mesAnnonces', [
                'title' => 'Créer une annonce',
                'errors' => [],
                'old' => [
                    'title' => $title ?? '',
                    'description' => $description ?? '',
                    'country' => $country ?? '',
                    'city' => $city ?? '',
                ]
            ]);
        }}








}
