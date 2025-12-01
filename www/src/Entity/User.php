<?php

declare(strict_types=1);

namespace App\Entity;

use JulienLinard\Doctrine\Mapping\Entity;
use JulienLinard\Doctrine\Mapping\Column;
use JulienLinard\Doctrine\Mapping\Id;
use JulienLinard\Doctrine\Mapping\Index;
use JulienLinard\Auth\Models\UserInterface;
use JulienLinard\Auth\Models\Authenticatable;
use JulienLinard\Doctrine\Mapping\ManyToOne;
use JulienLinard\Doctrine\Mapping\OneToMany;

#[Entity(table: 'users')]
class User implements UserInterface
{
    use Authenticatable;

    #[Id]
    #[Column(type: 'int',  autoIncrement: true)]
    public ?int $id = null;

    #[Column(type: 'string', length: 255)]
    #[Index(unique: true)]
    public string $email;

    #[Column(type: 'string', length: 255)]
    public string $password;

    #[Column(type: 'string', length: 100, nullable: true)]
    public ?string $first_name = null;

    #[Column(type: 'string', length: 100, nullable: true)]
    public ?string $last_name = null;

    #[Column(type: 'datetime', nullable: true)]
    public ?\DateTime $created_at = null;

    #[ManyToOne(targetEntity: Role::class, joinColumn: 'roles_id')]
    public ?string $roleId = '1';

    #[OneToMany(targetEntity: Reservation::class, mappedBy: 'guest', cascade: ['persist', 'remove'])]
    public array $reservations = [];

    public function getAuthRoles(): array|string
    {
        return $this->role ?? 'user';
    }

    /**
     * Retourne les permissions de l'utilisateur
     *
     * @return array
     */
    public function getAuthPermissions(): array
    {
        // Permissions basées sur le rôle
        return match ($this->roleId) {
            '2' => ['edit-posts', 'delete-posts', 'create-posts'],
            '1' => ['view-posts'],
            default => []
        };
    }
}