<?php

namespace Middleware\API;

use Helpers\Authenticate;
use Middleware\Middleware;
use Response\HTTPRenderer;
use Response\Render\JSONRenderer;

/**
 * ログイン済み&メールアドレス未検証の場合に後続の処理を実行する
 */
class EmailUnverifiedMiddleware implements Middleware {
    public function handle(callable $next): HTTPRenderer {
        error_log("Running authentication check...");

        if (!Authenticate::isLoggedIn() || Authenticate::emailVerified()) {
            $resBody = [
                "success" => false,
                "error" => "An error occurred.",
            ];
            return new JSONRenderer($resBody);
        }

        return $next();
    }
}
