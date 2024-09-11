<?php

namespace Middleware\UI;

use Helpers\Authenticate;
use Middleware\Middleware;
use Response\HTTPRenderer;
use Response\Render\RedirectRenderer;

/**
 * 未ログイン済みの場合に後続の処理を実行する
 */
class GuestMiddleware implements Middleware {
    public function handle(callable $next): HTTPRenderer {
        error_log("Running authentication check...");

        if (Authenticate::isLoggedIn()) {
            return new RedirectRenderer("/timeline");
        }

        return $next();
    }
}
