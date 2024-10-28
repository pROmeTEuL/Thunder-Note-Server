<?php
$db_arg = "dbname=thunder_note host=127.0.0.1";
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathComponents = explode('/', trim($path, '/'));
$id = isset($pathComponents[0]) && is_numeric($pathComponents[0]) ? (int)$pathComponents[0] : null;
if ($id !== null) {
    $db = pg_connect($db_arg);
    if (!$db) {
        header('HTTP/1.0 500 Internal Server Error');
        exit;
    }
    $result = pg_query($db, "SELECT * FROM notes WHERE id = $id");
    $resultArr = pg_fetch_all($result);
    if (empty($resultArr)) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }
    $json = json_encode($resultArr[0]);
    if ($json !== false) {
        header('HTTP/1.0 200 OK');
        echo $json;
    } else {
        header('HTTP/1.0 500 Internal Server Error');
    }
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $db = pg_connect($db_arg);
    if (!$db) {
        header('HTTP/1.0 500 Internal Server Error');
        exit;
    }
    $result = pg_query($db, "SELECT * FROM notes");
    $resultArr = pg_fetch_all($result);
    $json = json_encode($resultArr);
    if ($json !== false) {
        header('HTTP/1.0 200 OK');
        echo $json;
    } else {
        header('HTTP/1.0 500 Internal Server Error');
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $body = stream_get_contents(fopen('php://input', 'r'));
    if (empty($body)) {
        header('HTTP/1.0 400 Bad Request');
        exit;
    }
    $json = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        header('HTTP/1.0 400 Bad Request');
        exit;
    }
    $db = pg_connect($db_arg);
    if (!$db) {
        header('HTTP/1.0 500 Internal Server Error');
        exit;
    }
    printf("title: %s\n", $json['title']);
    printf("body: %s\n", $json['body']);
    $result = pg_query($db, "insert into notes (title, body) values ('{$json['title']}', '{$json['body']}')");
    if ($result) {
        header('HTTP/1.0 200 OK');
    } else {
        header('HTTP/1.0 500 Internal Server Error');
    }
} else {
    header('HTTP/1.0 400 Bad Request');
}
