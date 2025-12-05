<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Equipment;
use App\Entity\Room;
use App\Entity\User;
use App\Entity\User_Room;
use App\Repository\EquipmentRepository;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use App\Repository\User_RoomRepository; // Import nécessaire
use App\Service\FileUploadService;
use JulienLinard\Auth\AuthManager;
use JulienLinard\Auth\Middleware\AuthMiddleware;
use JulienLinard\Core\Controller\Controller;
use JulienLinard\Core\Session\Session;
use JulienLinard\Doctrine\EntityManager;
use JulienLinard\Doctrine\Repository\EntityRepository;
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

    #[Route(path: "/room/create", name: "app_room_create_form", methods: ["GET"], middleware: [AuthMiddleware::class])]
    public function createForm(): Response
    {

        $equipmentRepo = $this->em->createRepository(EquipmentRepository::class, Equipment::class);
        $allEquipments = $equipmentRepo->findAll();

        return $this->view('Annonces/createRoom', [
            'title' => 'Créer une annonce',
            'errors' => [],
            'old' => ['title' => '', 'description' => '', 'country' => '', 'city' => '', 'price_per_night' => '', 'number_of_bed' => ''],
            'allEquipments' => $allEquipments
        ]);
    }

    #[Route(path: "/room/create", name: "app_add_room", methods: ["POST"], middleware: [AuthMiddleware::class])]
    public function create(Request $request): Response
    {
        $user = $this->auth->user();
        if (!$user) {
            return $this->redirect('/login');
        }



        // 1. Récupération des données
        $title = trim($request->getPost('title', '') ?? '');
        $description = trim($request->getPost('description', '') ?? '');
        $country = trim($request->getPost('country', '') ?? '');
        $city = trim($request->getPost('city', '') ?? '');
        $price_per_night = (int)$request->getPost('price_per_night', 0);
        $number_of_bed = (int)$request->getPost('number_of_bed', 0);
        $type_of_room = trim($request->getPost('type_of_room', '') ?? '');
        $selectedEquipments = $request->getPost('equipments', []);

        // 2. Validation
        $errors = [];
        if (empty($title)) $errors['title'] = 'Le titre est requis';
        if (empty($country)) $errors['country'] = 'Le pays est requis';
        if (empty($city)) $errors['city'] = 'La ville est requise';
        if ($price_per_night <= 0) $errors['price_per_night'] = 'Le prix doit être positif';
        if ($number_of_bed <= 0) $errors['number_of_bed'] = 'Le nombre de lits est requis';
        $allowedTypes = [
            'appartement',
            'maison',
            'studio',
            'villa',
            'chalet',
            'bungalow',
            'loft',
            'duplex',
            'tiny_house',
            'mobil_home',
            'gite',
            'maison_hotes',
            'chambre_privee',
            'chambre_partagee',
            'penthouse'
        ];
        if (!in_array($type_of_room, $allowedTypes)) {
            $errors['type_of_room'] = 'Type de chambre invalide.';
        }

        $imagePath = null;

        // 3. Gestion de l'image
        if (isset($_FILES['media']) && $_FILES['media']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = $this->fileUploadService->upload($_FILES['media']);

            if ($uploadResult->isSuccess()) {
                $data = $uploadResult->getData();
                $imagePath = ltrim($data['path'], '/');
            } else {
                $errors['general'] = $uploadResult->getError();
            }
        }

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
            // 4. Création de la Room
            $room = new Room();
            $room->title = $title;
            $room->description = $description;
            $room->country = $country;
            $room->city = $city;
            $room->price_per_night = $price_per_night;
            $room->number_of_bed = $number_of_bed;
            $room->type_of_room = $type_of_room;
            $room->created_at = new \DateTime();
            $room->updated_at = new \DateTime();
            $room->is_reserved = false;
            $room->media_path = $imagePath;
            $room->equipments = [];


            $this->em->persist($room);
            $this->em->flush(); // FLUSH 1 : Indispensable pour avoir l'ID

            // GESTION DES ÉQUIPEMENTS (Correction pour votre ORM)
            $selectedEquipments = $request->getPost('equipments', []);

            if (is_array($selectedEquipments)) {
                // On récupère la connexion directe
                $conn = $this->em->getConnection();

                // A. NETTOYAGE : Suppression directe via execute()
                // Note: Pas de prepare(), on passe les paramètres directement
                $conn->execute(
                    "DELETE FROM room_equipments WHERE room_id = :room_id",
                    ['room_id' => $room->id]
                );

                // B. INSERTION
                if (!empty($selectedEquipments)) {
                    // On définit la requête SQL en chaîne de caractères
                    $sqlInsert = "INSERT INTO room_equipments (room_id, equipment_id) VALUES (:room_id, :equip_id)";

                    foreach ($selectedEquipments as $equipId) {
                        // On exécute la requête pour CHAQUE équipement
                        $conn->execute($sqlInsert, [
                            'room_id'  => $room->id,
                            'equip_id' => (int)$equipId
                        ]);
                    }
                }
            }

            // 5. Création de la liaison User_Room
            $userEntity = $this->em->createRepository(UserRepository::class, User::class)->find($user->id);
            // On s'assure que l'entité User est suivie
            $this->em->persist($userEntity);

            $userRoom = new User_Room();
            $userRoom->user = $userEntity;
            $userRoom->room = $room;
            $this->em->persist($userRoom);

            // 6. Changement de rôle (Si pas déjà Hôte)
            // Utilisation de la propriété string 'role'
            if ($userEntity->role !== 'hote') {
                $userEntity->role = 'hote';
                $userEntity->updated_at = new \DateTime();
                // $this->em->persist($userEntity); // Déjà persisté plus haut
            }

            // 7. FLUSH FINAL (Enregistre User_Room et l'UPDATE User)
            $this->em->flush();

            // 8. Mise à jour de la session pour refléter le nouveau rôle immédiatement
            if (method_exists($this->auth, 'login')) {
                $this->auth->login($userEntity);
            }

            Session::flash('success', 'Bien ajouté ! Vous êtes maintenant un Hôte.');
            return $this->redirect('/mesAnnonces');

        } catch (\Exception $e) {
            Session::flash('error', 'Erreur système : ' . $e->getMessage());
            return $this->redirect('/room/create');
        }
    }

    #[Route(path: "/room/edit", name: "app_edit_room_form", methods: ["GET"], middleware: [AuthMiddleware::class])]
    public function editForm(Request $request): Response
    {
        // CORRECTION ICI : Utilisation de EquipmentRepository
        $equipmentRepo = $this->em->createRepository(EquipmentRepository::class, Equipment::class);
        $allEquipments = $equipmentRepo->findAll();

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
        /** @var Room|null $room */
        $room = $roomRepo->find($id);

        if (!$room) {
            Session::flash('error', 'Annonce introuvable.');
            return $this->redirect('/mesAnnonces');
        }

        // IMPORTANT : Chargez les équipements existants de la chambre pour pré-cocher les cases !
        // (Sinon les cases seront vides même si la chambre a des équipements)
        $roomRepo->loadEquipments($room);

        // --- VÉRIFICATION DE SÉCURITÉ ---
        // Attention : Vérifiez bien le nom de votre repository UserRoom (souvent App\Repository\UserRoom)
        // Si votre fichier est UserRoom.php, la classe est probablement UserRoom et non User_RoomRepository
        $userRoomRepo = $this->em->createRepository(User_RoomRepository::class, User_Room::class);

        $isOwner = $userRoomRepo->findOneBy([
            'room_id' => $room->id,
            'user_id' => $user->id
        ]);

        if (!$isOwner) {
            Session::flash('error', 'Accès refusé. Vous n’êtes pas le propriétaire de cette annonce.');
            return $this->redirect('/mesAnnonces');
        }

        // 2. Rendre la vue
        return $this->view('Annonces/editRoom', [
            'room' => $room,
            'errors' => [],
            'allEquipments' => $allEquipments
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
        $type_of_room = trim($request->getPost('type_of_room', '') ?? '');

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
            $room->type_of_room = $type_of_room;
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
                type_of_room = :type,
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
                'type' => $room->type_of_room,
                'updated_at' => $room->updated_at->format('Y-m-d H:i:s'),
                'media_path' => $room->media_path,
                'id' => $room->id
            ];

            $connection->execute($sql, $params);

            // --- AJOUTER CE BLOC POUR SAUVEGARDER LES ÉQUIPEMENTS ---
            $selectedEquipments = $request->getPost('equipments', []);

            // 1. Nettoyage : On supprime les anciens équipements de cette chambre
            $connection->execute(
                "DELETE FROM room_equipments WHERE room_id = :room_id",
                ['room_id' => $room->id]
            );

            // 2. Insertion des nouveaux
            if (!empty($selectedEquipments) && is_array($selectedEquipments)) {
                $sqlInsert = "INSERT INTO room_equipments (room_id, equipment_id) VALUES (:room_id, :equip_id)";

                foreach ($selectedEquipments as $equipId) {
                    $connection->execute($sqlInsert, [
                        'room_id'  => $room->id,
                        'equip_id' => (int)$equipId
                    ]);
                }
            }
            // ---------------------------------------------------------

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
        $ownerData = $roomRepo->FindNamebyRoomId($room->id);


        $ownerName = $ownerData[0] ?? null;


        return $this->view('Annonces/showRoom', [
            'room' => $room,
            'ownerName' => $ownerName,
            'title' => $room->title . ' - Airbnb',
            'auth' => $this->auth,
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
            Session::flash('success', 'Annonce supprimé avec succès !');
            return $this->redirect('/mesAnnonces');
        } catch (\Exception $e) {
            Session::flash('error', 'Une erreur est survenue lors de la suppression du todo');
            return $this->redirect('/mesAnnonces');
        }

    }

    #[Route(path: "/room/search", name: "app_room_search", methods: ["POST"])]
    public function searchRoomsView(Request $request): Response
    {
        $textContent = trim($request->getPost('searchNavBar', ''));
        $textContent = preg_replace('/\s+/', ' ', $textContent);
        $textContent = strtolower($textContent);

        $roomRepo = $this->em->createRepository(RoomRepository::class, Room::class);

        $rooms = $roomRepo->findRoomBySearch($textContent);


        return $this->view('Annonces/searchAnnonces', [
            'title' =>  'Airbnb - Locations de vacances',
            'auth' => $this->auth,
            'rooms' => $rooms
        ]);
    }



}