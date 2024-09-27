<?php

use Database\DataAccess\DAOFactory;
use Helpers\Authenticator;
use Helpers\Hasher;
use Response\FlashData;
use Response\HTTPRenderer;
use Response\Render\HTMLRenderer;
use Response\Render\RedirectRenderer;
use Routing\Route;

return [
    "/" => Route::create("/", function(): HTTPRenderer {
        return new HTMLRenderer("page/top", []);
    })->setMiddleware(["guest"]),

    // ユーザー認証関連
    "/register" => Route::create("/register", function(): HTTPRenderer {
        return new HTMLRenderer("page/authentication/register", []);
    })->setMiddleware(["guest"]),
    "/login" => Route::create("/login", function(): HTTPRenderer {
        return new HTMLRenderer("page/authentication/login", []);
    })->setMiddleware(["guest"]),
    "/email/verification/resend" => Route::create("/email/verification/resend", function(): HTTPRenderer {
        return new HTMLRenderer("page/authentication/email_verification_resend", []);
    })->setMiddleware(["auth", "email_unverified"]),
    "/email/verify" => Route::create("/email/verify", function(): HTTPRenderer {
        try {
            $authenticatedUser = Authenticator::getAuthenticatedUser();

            // 署名に紐づくメールアドレスがログイン中ユーザーのメールアドレスと同じかを確認
            $hashedEmail = Hasher::createHash($authenticatedUser->getEmail());
            $expectedHashedEmail = $_GET["user"];
            if (!Hasher::isHashEqual($expectedHashedEmail, $hashedEmail)) {
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
            FlashData::setFlashData("error", "エラーが発生しました。");
            return new RedirectRenderer("/email/verification/resend");
        }
    })->setMiddleware(["auth", "email_unverified", "signature"]),
    "/password/forgot" => Route::create("/password/forgot", function(): HTTPRenderer {
        return new HTMLRenderer("page/authentication/password_forgot", []);
    })->setMiddleware(["guest"]),
    "/password/reset" => Route::create("/password/reset", function(): HTTPRenderer {
        try {
            $userDao = DAOFactory::getUserDAO();
            $tempUserDao = DAOFactory::getTempUserDAO();

            // signatureに紐づくユーザーを取得
            $tempUser = $tempUserDao->getBySignature($_GET["signature"]);
            if ($tempUser === null || $tempUser->getType() !== "PASSWORD_RESET") {
                FlashData::setFlashData("error", "無効なURLです。");
                return new RedirectRenderer("/");
            }
            $user = $userDao->getById($tempUser->getUserId());

            // 署名に紐づくメールアドレスがユーザーのメールアドレスと同じかを確認
            $hashedEmail = Hasher::createHash($user->getEmail());
            $expectedHashedEmail = $_GET["user"];
            if (!Hasher::isHashEqual($expectedHashedEmail, $hashedEmail)) {
                FlashData::setFlashData("error", "無効なURLです。");
                return new RedirectRenderer("/");
            }

            return new HTMLRenderer("page/authentication/password_reset", [
                "user" => $_GET["user"],
                "signature" => $_GET["signature"],
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            FlashData::setFlashData("error", "エラーが発生しました。");
            return new RedirectRenderer("/");
        }
    })->setMiddleware(["guest", "signature"]),

    // ユーザープロフィール関連
    "/user" => Route::create("/user", function(): HTTPRenderer {
        return new HTMLRenderer("page/profile/user", []);
    })->setMiddleware(["auth", "email_verified"]),
    "/user/followers" => Route::create("/user/followers", function(): HTTPRenderer {
        return new HTMLRenderer("page/profile/followers", []);
    })->setMiddleware(["auth", "email_verified"]),
    "/user/followees" => Route::create("/user/followees", function(): HTTPRenderer {
        return new HTMLRenderer("page/profile/followees", []);
    })->setMiddleware(["auth", "email_verified"]),

    // タイムライン関連
    "/timeline" => Route::create("/timeline", function(): HTTPRenderer {
        return new HTMLRenderer("page/timeline/timeline", []);
    })->setMiddleware(["auth", "email_verified"]),
];
