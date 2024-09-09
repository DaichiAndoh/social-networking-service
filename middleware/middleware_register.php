<?php

return [
    "global" => [
        \Middleware\GLOBAL\SessionsSetupMiddleware::class,
    ],
    "aliases" => [
        "guest" => \Middleware\UI\GuestMiddleware::class,
        "logged_in" => \Middleware\UI\LoggedInMiddleware::class,
        "unverified" => \Middleware\UI\EmailUnverifiedMiddleware::class,
        "auth" => \Middleware\UI\AuthenticatedMiddleware::class,
        "api_guest" => \Middleware\API\GuestMiddleware::class,
        "api_logged_in" => \Middleware\API\LoggedInMiddleware::class,
        "api_unverified" => \Middleware\API\EmailUnverifiedMiddleware::class,
        "api_auth" => \Middleware\API\AuthenticatedMiddleware::class,
    ],
];
