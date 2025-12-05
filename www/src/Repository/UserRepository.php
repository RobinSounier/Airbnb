<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use JulienLinard\Doctrine\Repository\EntityRepository;

class UserRepository extends EntityRepository
{
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Vérifie si un email existe déjà
     *
     * @param string $email Email à vérifier
     * @return bool True si l'email existe
     */
    public function emailExists(string $email): bool
    {
        $user = $this->findByEmail($email);
        return $user !== null;
    }

    /**
     * retourne l'id d'un user
     * @param string $user
     * @return int|null
     */

    public function getId(string $user): ?int
    {
        $user = $this->findByEmail($user);
        return $user->id;
    }

}