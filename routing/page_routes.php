<?php

use Helpers\Authenticate;
use Response\HTTPRenderer;
use Response\FlashData;
use Response\Render\HTMLRenderer;
use Response\Render\RedirectRenderer;

return [
    "/" => function(): HTTPRenderer {
        return new HTMLRenderer("page/top", []);
    },
    "/register" => function(): HTTPRenderer {
        if (Authenticate::isLoggedIn()) {
            FlashData::setFlashData("error", "Cannot register as you are already logged in.");
            if (Authenticate::emailVerified()) {
                return new RedirectRenderer("/");
            }
            return new RedirectRenderer("/");
        }
        return new HTMLRenderer("page/register", []);
    },
    "/login" => function(): HTTPRenderer {
        return new HTMLRenderer("page/login", []);
    },
];
