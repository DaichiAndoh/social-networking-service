<?php

use Response\HTTPRenderer;
use Response\Render\HTMLRenderer;
use Response\Render\JSONRenderer;

return [
    "/" => function(): HTTPRenderer {
        return new HTMLRenderer("page/top", []);
    },
    "/register" => function(): HTTPRenderer {
        return new HTMLRenderer("page/register", []);
    },
    "/login" => function(): HTTPRenderer {
        return new HTMLRenderer("page/login", []);
    },
    "/api" => function(): HTTPRenderer {
        return new JSONRenderer(["page" => "top"]);
    },
];
