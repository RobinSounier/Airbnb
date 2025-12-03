<?php

declare(strict_types=1);

namespace App\Entity;


use JulienLinard\Doctrine\Mapping\Column;
use JulienLinard\Doctrine\Mapping\Entity;
use JulienLinard\Doctrine\Mapping\Id;
use JulienLinard\Doctrine\Mapping\OneToMany; // Importation ajoutée

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

    // Relation One-To-Many vers User, mappée par la propriété 'role' dans User
    #[OneToMany(targetEntity: User::class, mappedBy: 'role', cascade: ['persist'])]
    public array $users = [];
}