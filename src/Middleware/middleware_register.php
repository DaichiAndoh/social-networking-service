<?php

return [
    "global" => [
        \Middleware\Global\SessionsSetupMiddleware::class,
    ],
    "ui-global" => [
        \Middleware\Ui\GetMethodRequestMiddleware::class,
    ],
    "api-global" => [
        \Middleware\Api\PostMethodRequestMiddleware::class,
    ],
    "aliases" => [
        // UI
        "signature" => \Middleware\Ui\SignatureValidationMiddleware::class,
        "guest" => \Middleware\Ui\GuestMiddleware::class,
        "auth" => \Middleware\Ui\AuthenticatedMiddleware::class,
        "email_verified" => \Middleware\Ui\EmailVerifiedMiddleware::class,
        "email_unverified" => \Middleware\Ui\EmailUnverifiedMiddleware::class,
        // API
        "api_guest" => \Middleware\Api\GuestMiddleware::class,
        "api_auth" => \Middleware\Api\AuthenticatedMiddleware::class,
        "api_email_verified" => \Middleware\Api\EmailVerifiedMiddleware::class,
        "api_email_unverified" => \Middleware\Api\EmailUnverifiedMiddleware::class,
    ],
];
