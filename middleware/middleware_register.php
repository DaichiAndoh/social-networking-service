<?php

return [
    "global" => [
        \Middleware\GLOBAL\SessionsSetupMiddleware::class,
    ],
    "aliases" => [
        "signature" => \Middleware\UI\SignatureValidationMiddleware::class,
        "guest" => \Middleware\UI\GuestMiddleware::class,
        "auth" => \Middleware\UI\AuthenticatedMiddleware::class,
        "api_guest" => \Middleware\API\GuestMiddleware::class,
        "api_auth" => \Middleware\API\AuthenticatedMiddleware::class,
    ],
];
