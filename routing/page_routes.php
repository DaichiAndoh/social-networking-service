<?php

use Database\DataAccess\DAOFactory;
use Helpers\Authenticate;
use Helpers\Settings;
use Response\HTTPRenderer;
use Response\FlashData;
use Response\Render\HTMLRenderer;
use Response\Render\RedirectRenderer;
use Routing\Route;

return [
    "/" => Route::create("/", function(): HTTPRenderer {
        return new HTMLRenderer("page/top", []);
    })->setMiddleware(["guest"]),
    "/register" => Route::create("/register", function(): HTTPRenderer {
        return new HTMLRenderer("page/register", []);
    })->setMiddleware(["guest"]),
    "/login" => Route::create("/login", function(): HTTPRenderer {
        return new HTMLRenderer("page/login", []);
    })->setMiddleware(["guest"]),
    "/password_forget" => Route::create("/password_forget", function(): HTTPRenderer {
        return new HTMLRenderer("page/password_forget", []);
    })->setMiddleware(["guest"]),
    "/password_reset" => Route::create("/password_reset", function(): HTTPRenderer {
        return new HTMLRenderer("page/password_reset", ["signature" => $_GET["signature"]]);
    })->setMiddleware(["guest", "signature"]),
    "/email/verification/resend" => Route::create("/email/verification/resend", function(): HTTPRenderer {
        return new HTMLRenderer("page/email_verification_resend", []);
    })->setMiddleware(["auth", "email_unverified"]),
    "/email/verify" => Route::create("/email/verify", function(): HTTPRenderer {
        try {
            $authenticatedUser = Authenticate::getAuthenticatedUser();

            // 署名に紐づくメールアドレスがログイン中ユーザーのメールアドレスと同じかを確認
            $hashedEmail = hash_hmac("sha256", $authenticatedUser->getEmail(), Settings::env("SIGNATURE_SECRET_KEY"));
            $expectedHashedEmail = $_GET["user"];
            if (!hash_equals($expectedHashedEmail, $hashedEmail)) {
                throw new Exception("署名に紐づくメールアドレスがログイン中ユーザーのメールアドレスと一致しません。");
            }

            // ユーザーのメールアドレス検証済み日時を設定
            $userDao = DAOFactory::getUserDao();
            $result = $userDao->updateEmailConfirmedAt($authenticatedUser->getUserId());
            if (!$result) throw new Exception("メールアドレス検証処理に失敗しました。");

            FlashData::setFlashData("success", "メールアドレス検証が完了しました。");
            return new RedirectRenderer("/timeline");
        } catch (Exception $e) {
            error_log($e->getMessage());
            FlashData::setFlashData("error", $e->getMessage());
            return new RedirectRenderer("/");
        }
    })->setMiddleware(["auth", "email_unverified"]),
    "/timeline" => Route::create("/timeline", function(): HTTPRenderer {
        return new HTMLRenderer("page/timeline", []);
    })->setMiddleware(["auth", "email_verified"]),
];
