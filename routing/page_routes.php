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
        if (Authenticate::isLoggedIn()) {
            FlashData::setFlashData("error", "Cannot register as you are already logged in.");
            if (Authenticate::emailVerified()) {
                return new RedirectRenderer("/");
            }
            return new RedirectRenderer("/");
        }
        return new HTMLRenderer("page/register", []);
    }),
    "/login" => Route::create("/login", function(): HTTPRenderer {
        if (Authenticate::isLoggedIn()) {
            FlashData::setFlashData("error", "Cannot login as you are already logged in.");
            if (Authenticate::emailVerified()) {
                return new RedirectRenderer("/");
            }
            return new RedirectRenderer("/");
        }
        return new HTMLRenderer("page/login", []);
    }),
];
