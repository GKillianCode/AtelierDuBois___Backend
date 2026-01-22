<?php

namespace App\Manager\User;

use App\Entity\User\Address;
use Doctrine\ORM\EntityManagerInterface;

class AddressManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function create(Address $address): void
    {
        $this->entityManager->persist($address);
        $this->entityManager->flush();
    }

    public function update(Address $address): void
    {
        $address->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    public function delete(Address $address): void
    {
        $this->entityManager->remove($address);
        $this->entityManager->flush();
    }
}
