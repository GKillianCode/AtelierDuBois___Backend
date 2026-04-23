<?php

namespace App\Voter;

use App\Entity\User\User;
use App\Enum\UserType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/** @extends Voter<string, mixed> */
class PlaceOrderVoter extends Voter
{
    public const CAN_PLACE_ORDER = 'CAN_PLACE_ORDER';

    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::CAN_PLACE_ORDER;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            $this->logger->warning('Unauthenticated access attempt', [
                'attribute'   => $attribute,
                'subject_id' => $subject->getId(),
            ]);

            return false;
        }

        return $user->getUserType() === UserType::CUSTOMER;
    }
}
