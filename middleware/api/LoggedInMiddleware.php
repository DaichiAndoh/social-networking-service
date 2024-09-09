<?php

namespace Middleware\API;

use Helpers\Authenticate;
use Middleware\Middleware;
use Response\HTTPRenderer;
use Response\Render\JSONRenderer;

/**
 * ログイン済みの場合に後続の処理を実行する
 */
class LoggedInMiddleware implements Middleware {
    public function handle(callable $next): HTTPRenderer {
        error_log("Running authentication check...");

        if (!Authenticate::isLoggedIn()) {
            $resBody = [
                "success" => false,
                "error" => "An error occurred.",
            ];
            return new JSONRenderer($resBody);
        }

        return $next();
    }
}
