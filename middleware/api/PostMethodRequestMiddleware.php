<?php

namespace Middleware\API;

use Middleware\Middleware;
use Response\HTTPRenderer;
use Response\Render\JSONRenderer;

/**
 * リクエストがPOSTメソッドの場合に後続の処理を実行する
 */
class PostMethodRequestMiddleware implements Middleware {
    public function handle(callable $next): HTTPRenderer {
        error_log("Running request method check...");

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $resBody = [
                "success" => false,
                "error" => "エラーが発生しました。",
            ];
            return new JSONRenderer($resBody);
        }

        return $next();
    }
}
