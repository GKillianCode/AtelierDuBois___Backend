<?php

namespace App\Util;

use Ramsey\Uuid\Uuid;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;

class UuidUtil
{
    private const BASE62 = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    public function __construct(
        public readonly LoggerInterface $logger
    ) {}

    public function generateUuid(): string
    {
        $this->logger->debug("UuidUtil::generateUuid ENTER");
        $uuidBase62 = Uuid::uuid4();
        $this->logger->debug("UuidUtil::generateUuid EXIT");
        return $uuidBase62;
    }

    public function generateUuid62(): string
    {
        $this->logger->debug("UuidUtil::generateUuid62 ENTER");
        $uuidBase62 = $this->uuidToBase62(Uuid::uuid4());
        $this->logger->debug("UuidUtil::generateUuid62 EXIT");
        return $uuidBase62;
    }

    public function uuidToBase62(UuidInterface $uuid): string
    {
        $this->logger->debug("UuidUtil::uuidToBase62 ENTER");
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

        // A 128-bit UUID always fits in 22 base-62 characters; pad with leading zeros
        // so the output length is constant and lexicographic order is preserved.
        $uuidBase62 = str_pad($base62, 22, '0', STR_PAD_LEFT);
        $this->logger->debug("UuidUtil::uuidToBase62 EXIT");
        return $uuidBase62;
    }
}
