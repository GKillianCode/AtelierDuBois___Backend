<?php

namespace App\Voter;

use App\Entity\User\User;
use App\Service\Product\ReviewRightsService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Centralizes review CRUD authorization by dispatching to ReviewRightsService.
 *
 * @extends Voter<string, ReviewVoterSubject>
 */
class ProductReviewVoter extends Voter
{
    public const REVIEW_ADD    = 'REVIEW_ADD';
    public const REVIEW_EDIT   = 'REVIEW_EDIT';
    public const REVIEW_DELETE = 'REVIEW_DELETE';

    private const ATTRIBUTES = [
        self::REVIEW_ADD,
        self::REVIEW_EDIT,
        self::REVIEW_DELETE,
    ];

    public function __construct(
        private readonly ReviewRightsService $reviewRightsService,
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, self::ATTRIBUTES, true)
            && $subject instanceof ReviewVoterSubject;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (!$token->getUser() instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::REVIEW_ADD    => $this->reviewRightsService->canAddReview(
                $subject->existingReview,
                $subject->purchaseDate ?? new \DateTimeImmutable(),
            ),
            self::REVIEW_EDIT   => $this->reviewRightsService->canEditReview($subject->existingReview),
            self::REVIEW_DELETE => $this->reviewRightsService->canDeleteReview($subject->existingReview),
            default             => false,
        };
    }
}
