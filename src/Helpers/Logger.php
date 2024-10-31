<?php

namespace Helpers;

class Logger {
    static function log(string $info): void {
        $timestamp = DateOperator::formatDateTime(DateOperator::getCurrentDateTime());
        fwrite(STDOUT, "[$timestamp] $info" . PHP_EOL);
    }
}
