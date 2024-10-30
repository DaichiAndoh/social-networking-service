<?php

namespace Helpers;

use Helpers\ConfigReader;

class Crypter {
    private static function getSecretKey(): string {
        return ConfigReader::env("CRYPT_SECRET_KEY");
    }

    public static function encrypt(string $value): string {
        $cipher = "aes-256-cbc";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $encrypted = openssl_encrypt($value, $cipher, self::getSecretKey(), 0, $iv);

        return base64_encode($iv . $encrypted);
    }

    public static function decrypt(string $value): string {
        $cipher = "aes-256-cbc";
        $ivlen = openssl_cipher_iv_length($cipher);

        $data = base64_decode($value);
        $iv = substr($data, 0, $ivlen);
        $encrypted = substr($data, $ivlen);

        return openssl_decrypt($encrypted, $cipher, self::getSecretKey(), 0, $iv);
    }
}
