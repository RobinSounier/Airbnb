<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Reservation;
use JulienLinard\Doctrine\EntityManager;
use JulienLinard\Doctrine\Repository\EntityRepository;

class ReservationRepository extends EntityRepository
{

    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * Vérifie si une Room est déjà réservée pendant la période donnée.
     * ...
     */
    public function isRoomReserved(int $roomId, \DateTime $startDate, \DateTime $endDate): bool
    {
        $startSql = $startDate->format('Y-m-d H:i:s');
        $endSql   = $endDate->format('Y-m-d H:i:s');

        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('*')
            ->from(Reservation::class, 'r')
            ->where('r.room_id = :room_id')
            ->andWhere('r.end_date > :start_date')
            ->andWhere('r.start_date < :end_date')
            ->setParameter('room_id', $roomId)
            ->setParameter('start_date', $startSql)
            ->setParameter('end_date', $endSql)
            ->setMaxResults(1);

        $result = $qb->getResult();

        return !empty($result);
    }
}