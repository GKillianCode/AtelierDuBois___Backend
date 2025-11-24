<?php

namespace App\EventListener;

use App\Entity\User\User;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsDoctrineListener(event: 'prePersist')]
#[AsDoctrineListener(event: 'preUpdate')]
class UserPasswordEncoderListener
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private readonly LoggerInterface $logger,
    ) {}

    public function prePersist(PrePersistEventArgs $args): void
    {
        $this->logger->debug("UserPasswordEncoderListener::prePersist ENTER");
        $this->encodePassword($args->getObject());
        $this->logger->debug("UserPasswordEncoderListener::prePersist EXIT");
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $this->logger->debug("UserPasswordEncoderListener::preUpdate ENTER");
        $this->encodePassword($args->getObject());
        $this->logger->debug("UserPasswordEncoderListener::preUpdate EXIT");
    }

    private function encodePassword(object $entity): void
    {
        $this->logger->debug("UserPasswordEncoderListener::encodePassword ENTER");
        if (!$entity instanceof User || null === $entity->getPlainPassword()) {
            return;
        }

        $entity->setPassword(
            $this->hasher->hashPassword($entity, $entity->getPlainPassword())
        );
        $entity->erasePlainPassword();
        $this->logger->debug("UserPasswordEncoderListener::encodePassword EXIT");
    }
}
