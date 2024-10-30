<?php

namespace Middleware\UI;

use Middleware\Middleware;
use Response\HTTPRenderer;
use Response\Render\RedirectRenderer;

/**
 * リクエストがGETメソッドの場合に後続の処理を実行する
 */
class GetMethodRequestMiddleware implements Middleware {
    public function handle(callable $next): HTTPRenderer {
        error_log("Running request method check...");

        if ($_SERVER["REQUEST_METHOD"] !== "GET") {
            return new RedirectRenderer("/");
        }

        return $next();
    }
}
