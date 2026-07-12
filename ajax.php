<?php
declare(strict_types=1);

require_once "./db.php";
require_once "./functions.php";
require_once "./Validator.php";

header("Content-Type: application/json; charset=UTF8");

$validator = new Validator();
if (!$validator->sanitize($_POST)) {
    http_response_code(400);
    echo json_encode(["error" => $validator->getFirstError()], JSON_UNESCAPED_UNICODE);
    exit;
}
$data = $validator->getData();

try {
    $pdo = getPdo();

    switch ($data['action']) {
        case 'create':
            $lastId = createTask($pdo, $data);
            echo json_encode(["success" => "Данные добавлены", "id" => $lastId], JSON_UNESCAPED_UNICODE);
            http_response_code(200);
            break;
        case 'update':
            if (updateTask($pdo, $data)) {
                echo json_encode(["success" => "Данные обновлены"], JSON_UNESCAPED_UNICODE);
                http_response_code(200);
            }
            break;
        case 'delete':
            if (deleteTask($pdo, $data)) {
                echo json_encode(["success" => "Задача удалена"], JSON_UNESCAPED_UNICODE);
                http_response_code(200);
            }
            break;
        case 'get':
            $tasks = getTasks($pdo, $data['offset'], $data['perpage']);
            // if (count($tasks) > 0) {
                echo json_encode($tasks);
                // echo json_encode(["success" => "Данные получены"], JSON_UNESCAPED_UNICODE);
                http_response_code(200);
            // }
            break;
        case 'count':
            echo json_encode(["count" => countAllTasks($pdo)]);
            http_response_code(200);
            break;
        default:
            echo json_encode(["error" => "Неизвестный action!"], JSON_UNESCAPED_UNICODE);
            http_response_code(400);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["error" => "Непредвиденная ошибка " . $e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
}
