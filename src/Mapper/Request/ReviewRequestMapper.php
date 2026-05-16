<?php

namespace App\Mapper\Request;

use App\Dto\Request\ReviewRequestDto;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReviewRequestMapper
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly TranslatorInterface $translator,
    ) {}

    public function mapAddReviewRequest(Request $request): ReviewRequestDto
    {
        $data = $this->extractJsonData($request);
        $this->validateAddPayload($data);

        return new ReviewRequestDto(
            rating: (int) $data['rating'],
            comment: isset($data['comment']) ? (string) $data['comment'] : null,
            productVariantPublicId: (string) $data['productVariantPublicId'],
        );
    }

    public function mapEditReviewRequest(Request $request): ReviewRequestDto
    {
        $data = $this->extractJsonData($request);
        $this->validateEditPayload($data);

        return new ReviewRequestDto(
            rating: (int) $data['rating'],
            comment: isset($data['comment']) ? (string) $data['comment'] : null,
        );
    }

    /** @return array<string, mixed> */
    private function extractJsonData(Request $request): array
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BadRequestException(
                $this->translator->trans('error.invalid_json', ['%detail%' => json_last_error_msg()], 'validators')
            );
        }

        return is_array($data) ? $data : [];
    }

    /** @param array<string, mixed> $data */
    private function validateAddPayload(array $data): void
    {
        $violations = $this->validator->validate($data, new Assert\Collection([
            'productVariantPublicId' => [
                new Assert\NotBlank(message: 'review.product_variant_public_id.not_blank'),
                new Assert\Type('string'),
                new Assert\Length(
                    min: 1,
                    max: 100,
                    maxMessage: 'review.product_variant_public_id.max_length',
                ),
            ],
            'rating' => [
                new Assert\NotNull(message: 'review.rating.not_null'),
                new Assert\Type('numeric'),
                new Assert\Range(
                    min: 1,
                    max: 5,
                    notInRangeMessage: 'review.rating.range',
                ),
            ],
            'comment' => new Assert\Optional([
                new Assert\Type('string'),
                new Assert\Length(
                    max: 255,
                    maxMessage: 'review.comment.max_length',
                ),
                new Assert\Regex(
                    pattern: '/^[\p{L}\p{N} ,.!?:()\[\]\-]*$/u',
                    message: 'review.comment.invalid_characters',
                ),
            ]),
        ]));

        if (\count($violations) > 0) {
            throw new BadRequestException(
                $this->translator->trans((string) $violations[0]->getMessage(), [], 'validators')
            );
        }
    }

    /** @param array<string, mixed> $data */
    private function validateEditPayload(array $data): void
    {
        $violations = $this->validator->validate($data, new Assert\Collection([
            'rating' => [
                new Assert\NotNull(message: 'review.rating.not_null'),
                new Assert\Type('numeric'),
                new Assert\Range(
                    notInRangeMessage: 'review.rating.range',
                    min: 1,
                    max: 5,
                ),
            ],
            'comment' => new Assert\Optional([
                new Assert\Type('string'),
                new Assert\Length(
                    max: 255,
                    maxMessage: 'review.comment.max_length',
                ),
                new Assert\Regex(
                    pattern: '/^[\p{L}\p{N} ,.!?:()\[\]\-]*$/u',
                    message: 'review.comment.invalid_characters',
                ),
            ]),
        ]));

        if (\count($violations) > 0) {
            throw new BadRequestException(
                $this->translator->trans((string) $violations[0]->getMessage(), [], 'validators')
            );
        }
    }
}
