<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Equipment;
use App\Entity\Room;
use App\Entity\User_Room;
use JulienLinard\Doctrine\EntityManager;
use JulienLinard\Doctrine\Repository\EntityRepository;

class RoomRepository extends EntityRepository
{
    protected EntityManager $em;

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
     * Retourne toutes les rooms liées à un utilisateur
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

    // --- AJOUTEZ CETTE MÉTHODE ---
    public function findAllRooms(): array
    {
        return $this->em->createQueryBuilder()
            ->select('*')
            ->from(Room::class, 'r')
            ->orderBy('r.created_at', 'DESC') // On affiche les plus récentes en premier
            ->getResult();
    }

    public function isRoomOwnedByUser(int $roomId, int $userId): bool
    {
        $qb = $this->em->createQueryBuilder('ur')
            ->select('COUNT(ur.id)')
            ->where('ur.room = :roomId')
            ->andWhere('ur.user = :userId')
            ->setParameter('roomID', $roomId)
            ->setParameter('userId', $userId);

        $result = $qb->getResult();

        return $count > 0;
    }

    /**
     * Méthode qui retourne les chambre grace a la recherche
     * @param string $search
     * @return array Resultat de la recherche
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


    public function loadEquipments(Room $room): void
    {
        // 1. On récupère l'objet PDO natif pour faire un SELECT facile
        $pdo = $this->em->getConnection()->getPdo();

        // 2. Requête SQL avec jointure
        $sql = "SELECT e.* FROM equipments e
                INNER JOIN room_equipments re ON e.id = re.equipment_id
                WHERE re.room_id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $room->id]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // 3. On hydrate (remplit) le tableau d'équipements de la Room
        $room->equipments = []; // On s'assure que c'est vide au départ

        foreach ($results as $row) {
            $equip = new Equipment();
            $equip->id = (int)$row['id'];
            $equip->name = $row['name'];
            $equip->icon = $row['icon'] ?? null;

            // On l'ajoute à la liste de la chambre
            $room->equipments[] = $equip;
        }
    }


}