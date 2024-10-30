<?php

namespace Middleware\UI;

use Helpers\Authenticator;
use Middleware\Middleware;
use Response\HTTPRenderer;
use Response\Render\RedirectRenderer;

/**
 * ログイン済みの場合に後続の処理を実行する
 */
class AuthenticatedMiddleware implements Middleware {
    public function handle(callable $next): HTTPRenderer {
        error_log("Running authentication check...");

        if (!Authenticator::isLoggedIn()) {
            return new RedirectRenderer("/login");
        }

        return $next();
    }
}
