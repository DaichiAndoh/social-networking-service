<?php

namespace Helpers;

use DateTime;

class DateOperator {
    public static function getTimeDiff(string $dateTimeString): string {
        // 現在時刻データ
        $now = new DateTime();

        // 比較対象の日時データ
        $dateTime = new DateTime($dateTimeString);

        // 経過時間を秒単位で取得
        $seconds = $now->getTimestamp() - $dateTime->getTimestamp();

        if ($seconds < 60) {
            // 1分未満
            return $seconds . "秒前";
        } elseif ($seconds < 3600) {
            // 1時間未満
            $minutes = floor($seconds / 60);
            return $minutes . "分前";
        } elseif ($seconds < 86400) {
            // 1日未満
            $hours = floor($seconds / 3600);
            return $hours . "時間前";
        } elseif ($seconds < 86400 * 7) {
            // 1週間未満
            $days = floor($seconds / 86400);
            return $days . "日前";
        } else {
            // 1週間以上の場合は日付を表示
            $format = $dateTime->format("Y") === $now->format("Y") ? "n月j日" : "Y年n月j日";
            return $dateTime->format($format);
        }
    }

    public static function stringToDatetime(string $dateTimeString): DateTime {
        return new DateTime($dateTimeString);
    }

    public static function formatJpDateTime(DateTime $datetime): string {
        return $datetime->format("Y年n月j日 G時i分");
    }
}
