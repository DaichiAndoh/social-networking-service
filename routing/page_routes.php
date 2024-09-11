<?php

use Database\DataAccess\DAOFactory;
use Helpers\Authenticate;
use Helpers\ValidationHelper;
use Response\HTTPRenderer;
use Response\FlashData;
use Response\Render\HTMLRenderer;
use Response\Render\RedirectRenderer;
use Routing\Route;
use Types\ValueType;

return [
    "/" => Route::create("/", function(): HTTPRenderer {
        return new HTMLRenderer("page/top", []);
    }),
    "/register" => Route::create("/register", function(): HTTPRenderer {
        return new HTMLRenderer("page/register", []);
    })->setMiddleware(["guest"]),
    "/login" => Route::create("/login", function(): HTTPRenderer {
        return new HTMLRenderer("page/login", []);
    })->setMiddleware(["guest"]),
    "/verify_email" => Route::create("/verify_email", function(): HTTPRenderer {
        $required_fields = [
            "id" => ValueType::INT,
            "user" => ValueType::STRING,
            "expiration" => ValueType::INT,
        ];

        $fieldErrors = ValidationHelper::validateFields($required_fields, $_GET);

        if (!empty($fieldErrors)) {
            FlashData::setFlashData("error", "無効なURLです。");
            return new RedirectRenderer("/");
        }

        $userDao = DAOFactory::getUserDAO();
        $user = $userDao->getById($_GET["id"]);
        echo print_r($user);
        $result = $userDao->updateEmailConfirmedAt($user->getUserId());
        if (!$result) throw new Exception("メールアドレス検証処理に失敗しました。");

        Authenticate::loginAsUser($user);

        FlashData::setFlashData("success", "アカウント登録が完了しました。");
        return new RedirectRenderer("/timeline");
    })->setMiddleware(["guest", "signature"]),
    "/timeline" => Route::create("/timeline", function(): HTTPRenderer {
        return new HTMLRenderer("page/timeline", []);
    })->setMiddleware(["auth"]),
];
