<?php

namespace Middleware\UI;

use Helpers\Authenticate;
use Middleware\Middleware;
use Response\HTTPRenderer;
use Response\Render\RedirectRenderer;

/**
 * ログイン済みの場合に後続の処理を実行する
 * ただし、メールアドレス未検証の場合は、検証待ち画面にリダイレクトする
 */
class EmailUnverifiedMiddleware implements Middleware {
    public function handle(callable $next): HTTPRenderer {
        error_log("Running authentication check...");

        if (!Authenticate::isLoggedIn()) {
            return new RedirectRenderer("/login");
        }

        $authenticatedUser = Authenticate::getAuthenticatedUser();
        if ($authenticatedUser->getEmailConfirmedAt() !== null) {
            return new RedirectRenderer("/timeline");
        }

        return $next();
    }
}
