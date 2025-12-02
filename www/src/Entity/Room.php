<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use JulienLinard\Doctrine\Mapping\Column;
use JulienLinard\Doctrine\Mapping\Entity;
use JulienLinard\Doctrine\Mapping\Id;
use JulienLinard\Doctrine\Mapping\ManyToOne;
use JulienLinard\Doctrine\Mapping\OneToMany;

#[Entity(table: 'rooms')]
class Room
{

    #[Id]
    #[Column(type: 'integer', autoIncrement: true)]
    public ?int $id = null;

    #[Column(type: 'string', length: 255, nullable: false)]
    public string $title;

    #[Column(type: 'string', length: 255, nullable: false)]
    public string $country;

    #[Column(type: 'string', length: 255, nullable: false)]
    public string $city;

    #[Column(type: 'int', nullable: false)]
    public int $price_per_night;

    #[Column(type: 'text', nullable: false)]
    public string $description;

    #[Column(type: 'int', nullable: false)]
    public int $number_of_bed = 1;

    #[Column(type: 'datetime', nullable: true, default: 'CURRENT_TIMESTAMP')]
    public ?\DateTime $created_at;

    #[Column(type: 'datetime', nullable: true, default: 'CURRENT_TIMESTAMP')]
    public ?\DateTime $updated_at;

    #[Column(type: 'bool', nullable: false, default: 'false')]
    public bool $is_reserved = false;
    
    #[OneToMany(targetEntity: User_Room::class, mappedBy: 'room', cascade: ['persist', 'remove'])]
    public array $userRooms = [];

    #[Column(type: "string", length: 255, nullable: true)]
    public ?string $media_path = null;

    #[OneToMany(targetEntity: Reservation::class, mappedBy: 'room', cascade: ['persist', 'remove'])]
    public array $reservations = [];
}