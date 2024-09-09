<?php

use Helpers\Authenticate;
use Response\HTTPRenderer;
use Response\FlashData;
use Response\Render\HTMLRenderer;
use Response\Render\RedirectRenderer;
use Routing\Route;

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
    "/verify_resend" => Route::create("/verify_resend", function(): HTTPRenderer {
        return new HTMLRenderer("page/verify_resend", []);
    })->setMiddleware(["unverified"]),
    "/timeline" => Route::create("/timeline", function(): HTTPRenderer {
        return new HTMLRenderer("page/timeline", []);
    })->setMiddleware(["auth"]),
];
