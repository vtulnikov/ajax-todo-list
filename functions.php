<?php
declare(strict_types=1);

function debug($data)
{
    echo '<pre>' . print_r($data, true) . '</pre>';
}
function createTask(PDO $pdo, array $data): int
{
    $sql = 'INSERT into `todolist` (`message`, `rank`) VALUES (?, ?)';
    $stmt = $pdo->prepare($sql);
    if (!$stmt->execute([$data["message"], $data["rank"]])) {
        http_response_code(400);
        throw new RuntimeException("Ошибка добавления пользователя");
    }
    return (int) $pdo->lastInsertId();
}
function updateTask(PDO $pdo, array $data):bool
{
    $keys = array_keys($data);

    //защита от инъекции, чтобы не попало ничего кроме 2-х возможных элементов
    $keyArr = array_intersect(["message", "rank"], $keys);
    $key = array_values($keyArr)[0] ?? null;

    if(!$key){
        http_response_code(400);
        throw new InvalidArgumentException("Неправильное значение для переданного ключа");
    }
    $sql = "UPDATE `todolist` set `{$key}` = ? WHERE id = ?"; //экранируем `{$key}` , т.к. rank зарезервированное слово и на некоторых серверах будет ошибка синтаксиса
    $stmt = $pdo->prepare($sql);

    if (!$stmt->execute([$data[$key], $data["id"]])) {
        http_response_code(400);
        throw new RuntimeException("Ошибка обновления данных!");
    } 
    return true;
}
function deleteTask(PDO $pdo, array $data):bool
{
    $sql = 'DELETE FROM `todolist` WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    if (!$stmt->execute([$data["id"]])) {
        http_response_code(400);
        throw new RuntimeException("Ошибка удаления пользователя");
    }
    return true;
}
function getTasks(PDO $pdo, int $offset = 0, int $perPage = 10):array
{
    $tasks = [];
    //`rank` нужно заэкранировать, т.к. это зарезервированное слово
    $sql = "SELECT * FROM `todolist` ORDER BY `rank` DESC LIMIT {$perPage} OFFSET {$offset}";
    $stmt = $pdo->query($sql);
    while ($row = $stmt->fetch()){
        $tasks[$row->id] = [
            "message" => $row->message,
            "rank" => $row->rank,
            "created_at" => $row->created_at,
        ];
    }
    return $tasks;
}
function countAllTasks(PDO $pdo):int
{
    $sql = "SELECT COUNT(*) FROM `todolist`";
    $stmt = $pdo->query($sql);
    return $stmt->fetchColumn();
}