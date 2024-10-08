<?php

namespace Helpers;

use Helpers\ConfigReader;

class Hasher {
    private static function getSecretKey(): string {
        return ConfigReader::env("SIGNATURE_SECRET_KEY");
    }

    public static function createHash(string $value): string {
        return hash_hmac("sha256", $value, self::getSecretKey());
    }

    public static function isHashEqual(string $expectedHashedValue, string $hashedValue): bool {
        return hash_equals($expectedHashedValue, $hashedValue);
    }
}
