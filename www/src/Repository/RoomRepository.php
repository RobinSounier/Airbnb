<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Room;
use App\Entity\User_Room;
use JulienLinard\Doctrine\EntityManager;
use JulienLinard\Doctrine\Repository\EntityRepository;

class RoomRepository extends EntityRepository
{
    protected EntityManager $em;

    public function __construct(EntityManager $em)
    {
        // On passe la Connection et MetadataReader au parent
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
}
