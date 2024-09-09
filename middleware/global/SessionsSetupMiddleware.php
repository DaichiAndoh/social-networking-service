<?php

namespace Middleware\GLOBAL;

use Middleware\Middleware;
use Response\HTTPRenderer;

class SessionsSetupMiddleware implements Middleware {
    public function handle(Callable $next): HTTPRenderer {
        error_log("Setting up sessions...");

        session_start();

        return $next();
    }
}
