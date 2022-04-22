<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

$parts = explode("/", $path);

$resource = $parts[1];

$id = $parts[2] ?? null;


$action = strtolower($_SERVER['REQUEST_METHOD']);

$controllerClass = '\\Controllers\\' . ucfirst($resource) . 'Controller';

if (class_exists($controllerClass)) {

    $database = new Database($_ENV["DB_HOST"], $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_PASS"]);

    $modelClass = '\\Models\\' . ucfirst($resource) . 'Model';

    $model = new $modelClass($database);

    $controller = new $controllerClass($model);

    $data = json_decode(file_get_contents('php://input'), true);
    
    if (strtolower($action) === 'post' && isset($data['credentials'])) {
        $controller->post();
        exit;
    }
    
    //Auth here 
    $jwt = new Jwt($_ENV['SECRET_KEY']);
    $auth = new Auth($jwt);

    if ($auth->authenticateAccessToken()) {
        if (method_exists($controller, $action)) {

            $controller->$action($id);
        } else {

            http_response_code(405);
            header("Allow: POST, GET, DELETE, PATCH");
            echo json_encode(['message' => 'Method not allowed.']);

            exit;
        }
    }
} else {
    http_response_code(404);
    echo json_encode(['message' => 'No found.']);

    exit;
}
