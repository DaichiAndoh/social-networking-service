<?php

namespace Middleware\API;

use Helpers\Authenticate;
use Middleware\Middleware;
use Response\HTTPRenderer;
use Response\Render\JSONRenderer;

/**
 * ログイン済みの場合に後続の処理を実行する
 * ただし、メールアドレス未検証の場合は、検証待ち画面にリダイレクトする
 */
class EmailVerifiedMiddleware implements Middleware {
    public function handle(callable $next): HTTPRenderer {
        error_log("Running authentication check...");

        if (!Authenticate::isLoggedIn()) {
            $resBody = [
                "success" => false,
                "error" => "エラーが発生しました。",
            ];
            return new JSONRenderer($resBody);
        }

        $authenticatedUser = Authenticate::getAuthenticatedUser();
        if ($authenticatedUser->getEmailConfirmedAt() === null) {
            $resBody = [
                "success" => false,
                "error" => "エラーが発生しました。",
            ];
            return new JSONRenderer($resBody);
        }

        return $next();
    }
}
