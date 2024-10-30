<?php

require_once __DIR__ . "/../vendor/autoload.php";

$DEBUG = true;

// リクエストURIを解析してパスだけを取得
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// ルートの読み込み
if (strpos($path, "/api/") === 0) {
    $routes = include(__DIR__ . "/../src/Routing/api_routes.php");
    $routeType = "api";
} else {
    $routes = include(__DIR__ . "/../src/Routing/page_routes.php");
    $routeType = "ui";
}

// ルートにパスが存在するかチェック
if (isset($routes[$path])) {
    try{
        // ルートの取得
        $route = $routes[$path];
        if (!($route instanceof Routing\Route)) throw new Exception("Invalid route type");

        // ミドルウェア読み込み
        $middlewareRegister = include(__DIR__ . "/../src/Middleware/middleware_register.php");
        $globalMiddlewares = array_merge($middlewareRegister["global"], $middlewareRegister[$routeType . "-global"]);
        $middlewares = array_merge(
            $globalMiddlewares,
            array_map(
                fn ($routeAlias) => $middlewareRegister["aliases"][$routeAlias],
                $route->getMiddleware()
            )
        );

        // 実行
        $middlewareHandler = new \Middleware\MiddlewareHandler(
            array_map(fn($middlewareClass) => new $middlewareClass(), $middlewares)
        );
        $renderer = $middlewareHandler->run($route->getCallback());

        // ヘッダーを設定
        foreach ($renderer->getFields() as $name => $value) {
            $sanitized_value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);

            if ($sanitized_value && $sanitized_value === $value) {
                header("{$name}: {$sanitized_value}");
            } else {
                http_response_code(500);
                if ($DEBUG) print("Failed setting header - original: '$value', sanitized: '$sanitized_value'");
                exit;
            }
        }

        print($renderer->getContent());
    } catch (Exception $e) {
        http_response_code(500);
        print("Internal error, please contact the admin.<br>");
        if ($DEBUG) print($e->getMessage());
    }
} else {
    http_response_code(404);
    echo "{$path} - 404 Not Found: The requested route was not found on this server.";
}
