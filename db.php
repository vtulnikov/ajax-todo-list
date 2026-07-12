<?php
declare(strict_types=1);

$config = [
    'host' => 'localhost',
    'db_user' => 'root',
    'db_name' => 'todolist',
    'password' => '',
    'charset' =>'UTF8',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    ],
];

function getPdo()
{
    global $config;
    $dsn = "mysql:host={$config['host']};dbname={$config['db_name']};charset={$config['charset']}";
    return new PDO($dsn, $config['db_user'], $config['password'], $config['options']);
}
