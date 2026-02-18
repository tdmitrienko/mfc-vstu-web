<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
    )
    {
    }

    /** @throws UniqueConstraintViolationException */
    public function createOrUpdateUserForAuth(string $email, string $mfcCode, array $roles, array $documents): ?User
    {
        $user = $this->userRepository->findByEmail($email);
        if ($user !== null) {
            $user->setMfcCode($mfcCode)
                ->setRoles($roles)
                ->setDocuments($documents)
            ;

            $this->userRepository->save($user);

            return $user;
        }

        $user = (new User())
            ->setEmail($email)
            ->setMfcCode($mfcCode)
            ->setRoles($roles)
            ->setDocuments($documents)
        ;

        $this->userRepository->save($user);

        return $user;
    }
}
