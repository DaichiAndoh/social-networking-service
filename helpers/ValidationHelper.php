<?php

namespace Helpers;

use Types\ValueType;
use Models\User;

class ValidationHelper {
    public static function validateFields(array $fields, array $data): array {
        $fieldErrors = [];

        foreach ($fields as $field => $type) {
            if (!isset($data[$field]) || ($data)[$field] === "") {
                $fieldErrors[$field] = "入力してください。";
            }

            $value = $data[$field];

            switch ($type) {
                case ValueType::STRING:
                    $result = is_string($value);
                    if ($result) {
                        if (!self::validateStrLen($value, 1, User::$valueMaxLen[$field])) {
                            $fieldErrors[$field] = sprintf("1文字以上,%s文字以下で入力してください。", User::$valueMaxLen[$field]);
                        }
                    } else {
                        $fieldErrors[$field] = "入力値が不適切です。";
                    }
                    break;

                case ValueType::EMAIL:
                    if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                        $fieldErrors[$field] = "無効なメールアドレスです。";
                    } else {
                        if (!self::validateStrLen($value, 1, User::$valueMaxLen[$field])) {
                            $fieldErrors[$field] = sprintf("1文字以上,%s文字以下で入力してください。", User::$valueMaxLen[$field]);
                        }
                    }
                    break;

                case ValueType::PASSWORD:
                    if (!(
                        is_string($value) &&
                        strlen($value) >= 8 && // 8文字以上
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

    public static function validateStrLen(string $value, int $min = 1, int $max = 100): bool {
        $len = strlen($value);
        return $len >= $min && $len <= $max;
    }
}
