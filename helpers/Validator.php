<?php

namespace Helpers;

use DateTime;
use Models\User;
use Types\ValueType;

require_once(sprintf("%s/../constants/file_constants.php", __DIR__));

class Validator {
    public static function validateFields(array $fields, array $data): array {
        $fieldErrors = [];

        foreach ($fields as $field => $type) {
            if (!isset($data[$field]) || ($data)[$field] === "") {
                $fieldErrors[$field] = "入力してください。";
            }

            $value = $data[$field];

            switch ($type) {
                case ValueType::STRING:
                    if (!is_string($value)) {
                        $fieldErrors[$field] = "無効な入力値です。";
                    }
                    break;

                case ValueType::INT:
                    if (!self::validateInteger($value)) {
                        $fieldErrors[$field] = "無効な入力値です。";
                    }
                    break;

                case ValueType::EMAIL:
                    if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                        $fieldErrors[$field] = "無効なメールアドレスです。";
                    }
                    break;

                case ValueType::PASSWORD:
                    if (!(
                        is_string($value) &&
                        strlen($value) >= 8 && // 8文字以上
                        strlen($value) <= 30 && // 30文字以下
                        preg_match("/[A-Z]/", $value) && // 1文字以上の大文字
                        preg_match("/[a-z]/", $value) && // 1文字以上の小文字
                        preg_match("/\d/", $value) && // 1文字以上の数値
                        preg_match("/[\W_]/", $value) // 1文字以上の特殊文字（アルファベット以外の文字）
                    )) {
                        $fieldErrors[$field] = "パスワードが条件を満たしていません。";
                    }
                    break;
            }
        }

        return $fieldErrors;
    }

    public static function validateInteger($value, float $min = -INF, float $max = INF): bool {
        if (!filter_var($value, FILTER_VALIDATE_INT)) return false;
        if ($value < $min || $value > $max) return false;
        return true;
    }

    public static function validateStrLen(string $value, int $min = 1, int $max = 100): bool {
        $len = strlen($value);
        return $len >= $min && $len <= $max;
    }

    public static function validateImageType(string $type): bool {
        $allowedTypes = ["image/png", "image/jpeg", "image/gif"];
        return in_array($type, $allowedTypes);
    }

    public static function validateImageSize(int $size, int $min = DEFAULT_FILE_MIN_SIZE, int $max = DEFAULT_FILE_MAX_SIZE): bool {
        return $size >= $min && $size <= $max;
    }

    public static function validateDateTime($datetime, $format = 'Y/m/d H:i'): bool {
        $dt = DateTime::createFromFormat($format, $datetime);
        return $dt && $dt->format($format) === $datetime;
    }
}
