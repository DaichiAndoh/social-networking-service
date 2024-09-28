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
        } else {
            // それ以上
            $days = floor($seconds / 86400);
            return $days . "日前";
        }
    }
}
