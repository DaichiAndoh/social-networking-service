<?php

use Database\DataAccess\DAOFactory;
use Helpers\Authenticate;
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
    "/verify_email" => Route::create("/verify_email", function(): HTTPRenderer {
        try {
            $userDao = DAOFactory::getUserDAO();
            $user = $userDao->getById($_GET["id"]);

            if ($user->getEmailConfirmedAt() !== null) throw new Exception("メールアドレスは検証済みです。");

            $result = $userDao->updateEmailConfirmedAt($user->getUserId());
            if (!$result) throw new Exception("メールアドレス検証処理に失敗しました。");

            Authenticate::loginAsUser($user);

            FlashData::setFlashData("success", "アカウント登録が完了しました。");
            return new RedirectRenderer("/timeline");
        } catch (Exception $e) {
            error_log($e->getMessage());
            FlashData::setFlashData("error", "エラーが発生しました。");
            return new RedirectRenderer("/");
        }
    })->setMiddleware(["guest", "signature"]),
    "/timeline" => Route::create("/timeline", function(): HTTPRenderer {
        return new HTMLRenderer("page/timeline", []);
    })->setMiddleware(["auth"]),
];
