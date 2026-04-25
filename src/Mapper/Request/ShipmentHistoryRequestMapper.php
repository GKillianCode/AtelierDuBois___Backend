<?php

namespace App\Mapper\Request;

use App\Dto\Request\Filter\GetShipmentHistoryRequestDto;
use App\Enum\SortFilter\ShipmentHistorySortFilterCode;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ShipmentHistoryRequestMapper
{
    public function __construct(
        private ValidatorInterface $validator,
        private TranslatorInterface $translator,
    ) {}

    public function mapGetAllShipmentsRequest(Request $request): GetShipmentHistoryRequestDto
    {
        $this->validateParameters($request);

        $filterValue = $request->query->get('filter');
        $filterNameValue = $request->query->get('filterName');
        $yearRaw = $request->query->get('year');

        return new GetShipmentHistoryRequestDto(
            page: (int) $request->query->get('page', 1),
            limit: min(100, max(1, (int) $request->query->get('limit', 20))),
            search: trim($request->query->get('search', '')),
            filter: $filterValue ? ShipmentHistorySortFilterCode::tryFrom($filterValue) : ShipmentHistorySortFilterCode::ORDERED_DESC,
            filterName: $filterNameValue ? ShipmentHistorySortFilterCode::tryFrom($filterNameValue) : ShipmentHistorySortFilterCode::NAME_ASC,
            year: $yearRaw !== null ? (int) $yearRaw : null,
        );
    }

    private function validateParameters(Request $request): void
    {
        $constraints = new Assert\Collection([
            'page' => [
                new Assert\Optional([
                    new Assert\Type('numeric'),
                    new Assert\Range(min: 1, max: 100000)
                ])
            ],
            'limit' => [
                new Assert\Optional([
                    new Assert\Type('numeric'),
                    new Assert\Choice(choices: ['20', '50', '100'], message: 'request.limit.choice')
                ])
            ],
            'search' => [
                new Assert\Optional([
                    new Assert\Type('string'),
                    new Assert\Length(max: 255),
                    new Assert\Regex(
                        pattern: '/^[\p{L}0-9 \-]*$/u',
                        message: 'request.search.regex'
                    )
                ])
            ],
            'filter' => [
                new Assert\Optional([
                    new Assert\Type('string'),
                    new Assert\Length(max: 50),
                    new Assert\Regex(
                        pattern: '/^[A-Z_]*$/',
                        message: 'request.filter.regex'
                    )
                ])
            ],
            'filterName' => [
                new Assert\Optional([
                    new Assert\Type('string'),
                    new Assert\Length(max: 50),
                    new Assert\Regex(
                        pattern: '/^[A-Z_]*$/',
                        message: 'request.filter.regex'
                    )
                ])
            ],
            'year' => [
                new Assert\Optional([
                    new Assert\Type('numeric'),
                    new Assert\Regex(
                        pattern: '/^(20\d{2}|2100)$/',
                        message: 'request.year.regex'
                    )
                ])
            ],
        ]);

        $violations = $this->validator->validate($request->query->all(), $constraints);

        if (\count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            throw new BadRequestException($this->translator->trans('error.validation_failed', ['%details%' => implode(', ', $errors)], 'validators'));
        }

        $this->validateEnumValues($request);
    }

    private function validateEnumValues(Request $request): void
    {
        $filterValue = $request->query->get('filter');
        if ($filterValue && !ShipmentHistorySortFilterCode::tryFrom($filterValue)) {
            throw new BadRequestException($this->translator->trans('request.filter.invalid', ['%value%' => $filterValue], 'validators'));
        }

        $filterNameValue = $request->query->get('filterName');
        if ($filterNameValue && $filterNameValue !== ShipmentHistorySortFilterCode::NAME_ASC->value && $filterNameValue !== ShipmentHistorySortFilterCode::NAME_DESC->value) {
            throw new BadRequestException($this->translator->trans('request.filter_name.invalid', ['%value%' => $filterNameValue], 'validators'));
        }
    }
}
