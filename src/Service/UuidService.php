<?php

namespace App\Service;

use Ramsey\Uuid\Uuid;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;

final class UuidService
{
    private const BASE62 = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    public function __construct(
        public readonly LoggerInterface $logger
    ) {}

    /**
     * Generate a new UUID in base 62 format
     * @return string
     */
    public function generateUuid62(): string
    {
        $this->logger->debug("UuidService::generateUuid62 ENTER");
        $uuidBase62 = $this->uuidToBase62(Uuid::uuid4());
        $this->logger->debug("UuidService::generateUuid62 EXIT");
        return $uuidBase62;
    }

    /**
     * Conversion of a UUID to base 62
     * @param UuidInterface $uuid
     * @return string
     */
    public function uuidToBase62(UuidInterface $uuid): string
    {
        $this->logger->debug("UuidService::uuidToBase62 ENTER");
        $bytes = $uuid->getBytes();
        $number = gmp_import($bytes);
        $base62 = '';

        while (gmp_cmp($number, 0) > 0) {
            $remainder = gmp_mod($number, 62);
            $base62 = self::BASE62[gmp_intval($remainder)] . $base62;
            $number = gmp_div_q($number, 62);
        }

        if ($base62 === '') {
            $base62 = '0';
        }

        $uuidBase62 = str_pad($base62, 22, '0', STR_PAD_LEFT);
        $this->logger->debug("UuidService::uuidToBase62 EXIT");
        return $uuidBase62;
    }
}
