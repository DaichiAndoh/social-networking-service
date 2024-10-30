<?php

return [
    "global" => [
        \Middleware\GLOBAL\SessionsSetupMiddleware::class,
    ],
    "ui-global" => [
        \Middleware\UI\GetMethodRequestMiddleware::class,
    ],
    "api-global" => [
        \Middleware\API\PostMethodRequestMiddleware::class,
    ],
    "aliases" => [
        // UI
        "signature" => \Middleware\UI\SignatureValidationMiddleware::class,
        "guest" => \Middleware\UI\GuestMiddleware::class,
        "auth" => \Middleware\UI\AuthenticatedMiddleware::class,
        "email_verified" => \Middleware\UI\EmailVerifiedMiddleware::class,
        "email_unverified" => \Middleware\UI\EmailUnverifiedMiddleware::class,
        // API
        "api_guest" => \Middleware\API\GuestMiddleware::class,
        "api_auth" => \Middleware\API\AuthenticatedMiddleware::class,
        "api_email_verified" => \Middleware\API\EmailVerifiedMiddleware::class,
        "api_email_unverified" => \Middleware\API\EmailUnverifiedMiddleware::class,
    ],
];
