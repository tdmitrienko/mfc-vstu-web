<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
    )
    {
    }

    public function findOrCreateUser(string $email): ?object
    {
        $user = $this->userRepository->findByEmail($email);
        if ($user !== null) {
            return $user;
        }

        $user = (new User())
            ->setEmail($email)
            ->setRoles(['ROLE_USER']);

        $this->userRepository->save($user);

        return $user;
    }
}
