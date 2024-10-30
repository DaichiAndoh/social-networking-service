<?php

namespace Middleware\GLOBAL;

use Middleware\Middleware;
use Response\HTTPRenderer;

class SessionsSetupMiddleware implements Middleware {
    public function handle(Callable $next): HTTPRenderer {
        error_log("Setting up sessions...");

        session_set_cookie_params([
            "lifetime" => 0,
            "httponly" => true,
            "samesite" => "Lax",
        ]);
        session_start();

        return $next();
    }
}
