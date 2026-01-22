<?php

namespace App\Manager\User;

use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;

class UserManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function create(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function update(User $user): void
    {
        $user->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    public function delete(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
