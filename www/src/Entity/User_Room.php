<?php
declare(strict_types=1);

namespace App\Entity;

use JulienLinard\Doctrine\Mapping\Entity;
use JulienLinard\Doctrine\Mapping\Id;
use JulienLinard\Doctrine\Mapping\Column;
use JulienLinard\Doctrine\Mapping\ManyToOne;

#[Entity(table: 'user_rooms')]
class User_Room
{
    #[Id]
    #[Column(type: 'int', name: 'id', autoIncrement: true)]
    public ?int $id = null; // <--- MODIFICATION ICI (ajout de ? et = null)

    #[ManyToOne(targetEntity: User::class, joinColumn: 'user_id')]
    public ?User $user = null;

    #[ManyToOne(targetEntity: Room::class, joinColumn: 'room_id')]
    public ?Room $room = null;
}