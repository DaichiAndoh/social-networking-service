<?php

namespace Middleware\UI;

use Helpers\ValidationHelper;
use Middleware\Middleware;
use Response\FlashData;
use Response\HTTPRenderer;
use Response\Render\RedirectRenderer;
use Routing\Route;

/**
 * URL署名が有効である場合に後続の処理を実行する
 */
class SignatureValidationMiddleware implements Middleware {
    public function handle(callable $next): HTTPRenderer {
        $currentPath = $_SERVER["REQUEST_URI"] ?? "";
        $parsedUrl = parse_url($currentPath);
        $pathWithoutQuery = $parsedUrl["path"] ?? "";

        // 現在のパスのRouteオブジェクトを作成
        $route = Route::create($pathWithoutQuery, function() {});

        $protocol = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off" || $_SERVER["SERVER_PORT"] == 443) ? "https://" : "http://";
        $host = $_SERVER["HTTP_HOST"];
        $url = $protocol . $host . $currentPath;

        // URLに有効な署名があるかチェック
        if ($route->isSignedURLValid($url)) {
            // 有効期限があるかどうかを確認し、有効期限がある場合は有効期限が切れていないことを確認
            if (isset($_GET["expiration"]) && $_GET["expiration"] < time()) {
                FlashData::setFlashData("error", "URLの有効期限が切れています。");
                return new RedirectRenderer("/");
            }

            return $next();
        } else {
            // 署名が有効でない場合、トップページにリダイレクト
            FlashData::setFlashData("error", "無効なURLです。");
            return new RedirectRenderer("/");
        }
    }
}
