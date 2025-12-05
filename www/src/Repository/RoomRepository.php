<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Equipment;
use App\Entity\Room;
use App\Entity\Room_Equipment;
use App\Entity\User;
use App\Entity\User_Room;
use JulienLinard\Doctrine\EntityManager;
use JulienLinard\Doctrine\Repository\EntityRepository;

class RoomRepository extends EntityRepository
{
    protected EntityManager $em;

    /**
     * Initialise le Repository.
     *
     * @param EntityManager $em L'Entity Manager.
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct(
            $em->getConnection(),
            $em->getMetadataReader(),
            Room::class
        );
        $this->em = $em;
    }

    /**
     * Retourne toutes les rooms liées à un utilisateur.
     *
     * @param int $userId ID de l'utilisateur (propriétaire).
     * @return array Liste des entités Room (format tableau).
     */
    public function findByUser(int $userId): array
    {
        $qb = $this->em->createQueryBuilder();

        return $qb->select('*')
            ->from(Room::class, 'r')
            ->whereSubquery('r.id', 'IN', function($subQb) use ($userId) {
                $subQb->from(User_Room::class, 'ur')
                    ->select('ur.room_id')
                    ->where('ur.user_id = ?', $userId);
            })
            ->getResult();
    }

    /**
     * Récupère toutes les annonces (Rooms) du site.
     *
     * @return array Liste des entités Room (format tableau).
     */
    public function findAllRooms(): array
    {
        return $this->em->createQueryBuilder()
            ->select('*')
            ->from(Room::class, 'r')
            ->orderBy('r.created_at', 'DESC') // On affiche les plus récentes en premier
            ->getResult();
    }

    /**
     * Vérifie si une annonce spécifique appartient à un utilisateur donné.
     *
     * @param int $roomId ID de l'annonce.
     * @param int $userId ID de l'utilisateur.
     * @return bool True si l'utilisateur est le propriétaire.
     */
    public function isRoomOwnedByUser(int $roomId, int $userId): bool
    {
        $qb = $this->em->createQueryBuilder('ur')
            ->select('COUNT(ur.id)')
            ->where('ur.room = :roomId')
            ->andWhere('ur.user = :userId')
            ->setParameter('roomID', $roomId)
            ->setParameter('userId', $userId);

        $result = $qb->getResult();

        return $result[0] > 0;
    }

    /**
     * Récupère les chambres correspondant au terme de recherche (titre, pays, ville).
     *
     * @param string $search Terme de recherche.
     * @return array Liste des résultats (format tableau).
     */
    public function findRoomBySearch(string $search): array
    {
        $qb = $this->em->createQueryBuilder('r')
            ->select('*')
            ->from(Room::class, 'r')
            ->where('r.title LIKE :search')
            ->orWhere('r.country LIKE :search1')
            ->orWhere('r.city LIKE :search2')
            ->setParameter('search', '%' . $search . '%')
            ->setParameter('search1', '%' . $search . '%')
            ->setParameter('search2', '%' . $search . '%');

        return $qb->getResult();
    }

    /**
     * Charge manuellement les équipements associés à une Room.
     *
     * @param Room $room L'entité Room à hydrater.
     * @return void
     */
    public function loadEquipments(Room $room): void
    {
        $pdo = $this->em->getConnection()->getPdo();

        $sql = "SELECT e.* FROM equipments e
                INNER JOIN room_equipments re ON e.id = re.equipment_id
                WHERE re.room_id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $room->id]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $room->equipments = [];

        foreach ($results as $row) {
            $equip = new Equipment();
            $equip->id = (int)$row['id'];
            $equip->name = $row['name'];
            $equip->icon = $row['icon'] ?? null;

            $room->equipments[] = $equip;
        }
    }

    /**
     * Récupère le prénom et le nom du propriétaire d'une annonce.
     *
     * @param int $roomID ID de l'annonce.
     * @return array|null Tableau contenant 'first_name' et 'last_name' ou null.
     */
    public function FindNamebyRoomId(int $roomID): ?array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('u.first_name, u.last_name')
            ->from(User::class, 'u')
            ->join(User_Room::class, 'ur', 'u.id = ur.user_id')
            ->join(Room::class, 'r', 'r.id = ur.room_id')
            ->where('r.id = :roomId')
            ->setParameter('roomId', $roomID)

            ->setMaxResults(1);

        // Retourne le premier résultat (la ligne) ou null
        $result = $qb->getResult();
        return $result[0] ?? null;
    }


    /**
     * Récupère les chambres en fonction des critères de filtrage.
     *
     * @param array $filters Tableau des critères (minPrice, city, equipmentIds, etc.).
     * @return array Liste des entités Room ou tableaux associatifs.
     */
    public function findRoomsByFilters(array $filters = []): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('r.*')
            ->from(Room::class, 'r');

        // --- DÉMARRAGE DE LA CLAUSE WHERE (CORRECTION DU BUG AND) ---
        // On initialise avec une condition toujours vraie pour que les AND suivants fonctionnent.
        $qb->where('1 = 1');

        // --- 1. Filtres Locaux et Type ---
        if (!empty($filters['country'])) {
            $qb->andWhere('r.country LIKE :country')
                ->setParameter('country', '%' . $filters['country'] . '%');
        }
        if (!empty($filters['city'])) {
            $qb->andWhere('r.city LIKE :city')
                ->setParameter('city', '%' . $filters['city'] . '%');
        }
        if (!empty($filters['roomType'])) {
            $qb->andWhere('r.type_of_room = :type')
                ->setParameter('type', $filters['roomType']);
        }

        // --- 2. Filtres Prix ---
        if (isset($filters['minPrice']) && is_numeric($filters['minPrice'])) {
            $qb->andWhere('r.price_per_night >= :minPrice')
                ->setParameter('minPrice', $filters['minPrice']);
        }
        if (isset($filters['maxPrice']) && is_numeric($filters['maxPrice'])) {
            $qb->andWhere('r.price_per_night <= :maxPrice')
                ->setParameter('maxPrice', $filters['maxPrice']);
        }

        // --- 3. Filtre Équipements (Logique AND via Sous-requête SQL) ---
        if (!empty($filters['equipmentIds'])) {
            $equipmentCount = count($filters['equipmentIds']);

            $equipmentIds = array_map('intval', (array)$filters['equipmentIds']);

            // Construction de la sous-requête SQL brute pour trouver les IDs de Room
            // qui correspondent à TOUS les équipements requis (GROUP BY + HAVING COUNT)
            $sqlSubquery = "
                SELECT re.room_id FROM room_equipments re
                WHERE re.equipment_id IN (" . implode(', ', $equipmentIds) . ")
                GROUP BY re.room_id
                HAVING COUNT(re.room_id) = {$equipmentCount}
            ";

            // Ajout de la condition à la requête principale
            $qb->andWhere('r.id IN (' . $sqlSubquery . ')');
        }

        $qb->orderBy('r.created_at', 'DESC');

        return $qb->getResult();
    }

}