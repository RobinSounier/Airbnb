<?php
declare(strict_types=1);

namespace App\Entity;

use JulienLinard\Doctrine\Mapping\Column;
use JulienLinard\Doctrine\Mapping\Entity;
use JulienLinard\Doctrine\Mapping\Id;

#[Entity(table: 'equipments')]
class Equipment
{
    #[Id]
    #[Column(type: 'integer', autoIncrement: true)]
    public ?int $id = null;

    #[Column(type: 'string', length: 50)]
    public string $name;

    #[Column(type: 'string', length: 50, nullable: true)]
    public ?string $icon = null;
}
