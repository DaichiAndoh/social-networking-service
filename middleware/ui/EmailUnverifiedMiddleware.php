<?php

namespace Middleware\UI;

use Helpers\Authenticate;
use Middleware\Middleware;
use Response\HTTPRenderer;
use Response\Render\RedirectRenderer;

/**
 * ログイン済み&メールアドレス未検証の場合に後続の処理を実行する
 */
class EmailUnverifiedMiddleware implements Middleware {
    public function handle(callable $next): HTTPRenderer {
        error_log("Running authentication check...");

        if (!Authenticate::isLoggedIn()) {
            return new RedirectRenderer("/login");
        } else if (Authenticate::emailVerified()) {
            return new RedirectRenderer("/timeline");
        }

        return $next();
    }
}
