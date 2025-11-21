<?php

namespace App\EventListener;

use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsDoctrineListener(event: 'prePersist')]
#[AsDoctrineListener(event: 'preUpdate')]
class UserPasswordEncoderListener
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function prePersist(PrePersistEventArgs $args): void
    {
        $this->encodePassword($args->getObject());
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $this->encodePassword($args->getObject());
    }

    private function encodePassword(object $entity): void
    {
        if (!$entity instanceof User || null === $entity->getPlainPassword()) {
            return;
        }

        $entity->setPassword(
            $this->hasher->hashPassword($entity, $entity->getPlainPassword())
        );
        $entity->erasePlainPassword();
    }
}
