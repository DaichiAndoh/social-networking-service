<?php

use Response\HTTPRenderer;
use Response\Render\HTMLRenderer;
use Response\Render\JSONRenderer;

return [
    "/" => function(): HTTPRenderer {
        return new HTMLRenderer("page/top", []);
    },
    "/api" => function(): HTTPRenderer {
        return new JSONRenderer(["page" => "top"]);
    },
];
