<?php

declare(strict_types=1);

namespace App\Entity;


use DateTime;
use JulienLinard\Doctrine\Mapping\Entity;
use JulienLinard\Doctrine\Mapping\Column;
use JulienLinard\Doctrine\Mapping\Id;
use JulienLinard\Doctrine\Mapping\Index;
use JulienLinard\Auth\Models\UserInterface;
use JulienLinard\Auth\Models\Authenticatable;
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

    #[Column(type: 'string', length: 100, nullable: false)]
    public string $first_name;

    #[Column(type: 'string', length: 100, nullable: false)]
    public string $last_name;

    #[Column(type: 'datetime', nullable: false)]
    public DateTime $created_at;

    #[Column(type: 'datetime', nullable: true)]
    public ?DateTime $updated_at = null;

    #[Column(type: 'string', length: 50, nullable: false, default: 'user')]
    public string $role = 'user';

    #[OneToMany(targetEntity: User_Room::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    public array $userRooms = [];


    #[OneToMany(targetEntity: Reservation::class, mappedBy: 'guest', cascade: ['persist', 'remove'])]
    public array $reservations = [];



    public function getAuthRoles(): array|string
    {
        return $this->role;
    }

    public function getAuthPermissions(): array
    {
        return match ($this->role) {
            'hote' => ['edit-posts', 'delete-posts', 'create-posts'],
            'user' => ['view-posts'],
            default => []
        };
    }


}