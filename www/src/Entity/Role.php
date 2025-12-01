<?php

declare(strict_types=1);

namespace App\Entity;


use JulienLinard\Doctrine\Mapping\Column;
use JulienLinard\Doctrine\Mapping\Entity;
use JulienLinard\Doctrine\Mapping\Id;

#[Entity(table: 'roles')]
class Role
{

    #[Id]
    #[Column(type: 'integer', autoIncrement: true)]
    public ?int $id = null;

    #[Column(type: 'string', length: 255)]
    public ?string $name = null;

    #[Column(type: 'string', length: 255, nullable: true)]
    public ?string $description = null;
}