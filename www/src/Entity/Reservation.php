<?php

declare(strict_types=1);

namespace App\Entity;


use DateTime;
use JulienLinard\Doctrine\Mapping\Column;
use JulienLinard\Doctrine\Mapping\Entity;
use JulienLinard\Doctrine\Mapping\Id;
use JulienLinard\Doctrine\Mapping\ManyToOne;

#[Entity(table: 'reservations')]
class Reservation
{

    #[Id]
    #[Column(type: 'integer', autoincrement: true)]
    public ?int $id;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'reservations')]
    public ?User $guest = null;

    #[ManyToOne(targetEntity: Room::class, inversedBy: 'reservations')]
    public ?Room $room = null;

    #[Column(type: 'datetime')]
    public \DateTime $start_date;

    #[Column(type: 'datetime')]
    public \DateTime $end_date;

    #[Column(type: 'varchar', length: 255)]
    public string $comment;

    #[Column(type: 'datetime')]
    public DateTime $created_at;

}
