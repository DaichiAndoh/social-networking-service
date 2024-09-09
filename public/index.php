<?php
spl_autoload_extensions(".php");
spl_autoload_register(function($name) {
    $filepath = __DIR__ . "/../" . str_replace("\\", "/", $name) . ".php";
    require_once $filepath;
});

session_start();

$DEBUG = true;

// リクエストURIを解析してパスだけを取得
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// ルートの読み込み
if (strpos($path, "/api/") === 0) {
    $routes = include("../routing/api_routes.php");
} else {
    $routes = include("../routing/page_routes.php");
}

// ルートにパスが存在するかチェック
if (isset($routes[$path])) {
    try{
        // ルートの取得
        $route = $routes[$path];
        if (!($route instanceof Routing\Route)) throw new Exception("Invalid route type");

        // ミドルウェア読み込み
        $middlewareRegister = include("../middleware/middleware_register.php");
        $middlewares = array_merge(
            $middlewareRegister["global"],
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
