<?php

namespace App\Voter;

use App\Entity\Order\Order;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/** @extends Voter<string, Order> */
class CancelShipmentVoter extends Voter
{
    public const CAN_CANCEL_SHIPMENT = 'CAN_CANCEL_SHIPMENT';

    public function __construct(
        private readonly int $shipmentCancellationDelayHours,
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::CAN_CANCEL_SHIPMENT && $subject instanceof Order;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        $createdAt = $subject->getCreatedAt();
        if ($createdAt === null) {
            return false;
        }

        $deadline = (new \DateTimeImmutable())->modify(
            sprintf('-%d hours', $this->shipmentCancellationDelayHours)
        );

        return $createdAt >= $deadline;
    }
}
