<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\Room;
use App\Entity\User;
use App\Entity\User_Room;
use JulienLinard\Auth\AuthManager;
use JulienLinard\Doctrine\EntityManager;
use JulienLinard\Doctrine\Repository\EntityRepository;

class ReservationRepository extends EntityRepository
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
            Reservation::class
        );
        $this->em = $em;
    }


    /**
     * Vérifie si une Room est déjà réservée pendant la période donnée.
     *
     * @param int $roomId ID du logement à vérifier.
     * @param \DateTime $startDate Date de début de la période.
     * @param \DateTime $endDate Date de fin de la période.
     * @return bool True si une réservation chevauche la période.
     */
    public function isRoomReserved(int $roomId, \DateTime $startDate, \DateTime $endDate): bool
    {
        $startSql = $startDate->format('Y-m-d H:i:s');
        $endSql   = $endDate->format('Y-m-d H:i:s');

        $qb = $this->em->createQueryBuilder();

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


    /**
     * Récupère les réservations reçues par l'user.
     *
     * @param EntityManager $em L'Entity Manager.
     * @param User $user L'utilisateur (user) connecté.
     * @return array Liste des réservations reçues (format tableau).
     */
    public function findHostReservations(EntityManager $em, User $user): array
    {
        $hostId = $user->id;

        $qb = $em->createQueryBuilder()
            ->select('*')
            ->from(Reservation::class, 'r')
            ->join(Room::class, 'ro', 'r.room_id = ro.id')
            ->where('r.guest_id = :host_id')
            ->setParameter('host_id', $hostId)
            ->orderBy('r.start_date', 'ASC');

        return $qb->getResult();

    }

    /**
     * Récupère toutes les réservations reçues par un hôte pour ses biens.
     * Retourne un tableau associatif avec les détails nécessaires à la vue.
     *
     * @param int $hostId ID de l'hôte.
     * @return array Liste des réservations reçues.
     */
    public function findReservationsByHost(User $hostId): array
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select('
                r.id, start_date, end_date, comment, r.created_at,
                rm.title as room_title, rm.media_path, rm.price_per_night,
                u.first_name as guest_firstname, u.last_name as guest_lastname, u.email as guest_email
           ')
            ->from(Reservation::class, 'r')
            ->join(Room::class, 'rm', 'r.room_id = rm.id')
            ->join(User_Room::class, 'ur', 'rm.id = ur.room_id')
            ->join(User::class, 'u', 'u.id = r.guest_id')
            ->where('ur.user_id = :hostId')
            ->setParameter('hostId', $hostId)
            ->orderBy('r.start_date', 'ASC');

        return $qb->getResult();
    }
}