<?php
declare(strict_types=1);

namespace App\Entity;

use JulienLinard\Doctrine\Mapping\Entity;
use JulienLinard\Doctrine\Mapping\Id;
use JulienLinard\Doctrine\Mapping\ManyToOne;

/**
 * Entité de liaison Many-to-Many entre Room et Equipment
 */
#[Entity(table: 'room_equipments')]
class Room_Equipment
{
    /**
     * @var Room|null La chambre associée (fait partie de la clé primaire composite).
     */
    #[Id]
    #[ManyToOne(targetEntity: Room::class, joinColumn: 'room_id')]
    public ?Room $room = null;

    /**
     * @var Equipment|null L'équipement associé (fait partie de la clé primaire composite).
     */
    #[Id]
    #[ManyToOne(targetEntity: Equipment::class, joinColumn: 'equipment_id')]
    public ?Equipment $equipment = null;
}
