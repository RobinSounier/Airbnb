<?php

declare(strict_types=1);

namespace App\Entity;

use JulienLinard\Doctrine\Mapping\Entity;
use JulienLinard\Doctrine\Mapping\Id;
use JulienLinard\Doctrine\Mapping\ManyToOne;

#[Entity(table: 'user_rooms')]
class User_Room
{
    #[Id]
    #[ManyToOne(targetEntity: User::class, joinColumn: 'user_id')]
    private ?User $user = null;

    #[Id]
    #[ManyToOne(targetEntity: Room::class, joinColumn: 'room_id')]
    private ?Room $room = null;
}

