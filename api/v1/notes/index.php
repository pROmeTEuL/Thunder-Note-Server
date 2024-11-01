<?php
$config = parse_ini_file('../../../settings.conf');
if ($config === false) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}
if (!isset($config['user'])) {
    header('HTTP/1.0 500 Internal Server Error');
    exit;
}
if (isset($config['password'])) {
    $db_arg = "dbname=thunder_note host=127.0.0.1 user={$config['user']} password={$config['password']}";
} else {
    $db_arg = "dbname=thunder_note host=127.0.0.1 user={$config['user']}";
}
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathComponents = explode('/', trim($path, '/'));
$id = isset($pathComponents[3]) && is_numeric($pathComponents[3]) ? (int)$pathComponents[3] : null;
if ($id !== null) {
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
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
    } else if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $db = pg_connect($db_arg);
        if (!$db) {
            header('HTTP/1.0 500 Internal Server Error');
            exit;
        }
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
        $currentDate = date('Y-m-d H:i');
        $result = pg_query($db, "UPDATE notes SET title = '{$json['title']}', body = '{$json['body']}', date = '$currentDate' WHERE id = $id");
        if ($result) {
            header('HTTP/1.0 200 OK');
        } else {
            header('HTTP/1.0 500 Internal Server Error');
        }
        exit;
    } else if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
        $db = pg_connect($db_arg);
        if (!$db) {
            header('HTTP/1.0 500 Internal Server Error');
            exit;
        }
        $result = pg_query($db, "DELETE FROM notes WHERE id = $id");
        if ($result) {
            header('HTTP/1.0 200 OK');
        } else {
            header('HTTP/1.0 500 Internal Server Error');
        }
        exit;
    }
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
    $currentDate = date('Y-m-d H:i');
    $result = pg_query($db, "insert into notes (title, body, date) values ('{$json['title']}', '{$json['body']}', '$currentDate')");
    if ($result) {
        header('HTTP/1.0 200 OK');
    } else {
        header('HTTP/1.0 500 Internal Server Error');
    }
} else {
    header('HTTP/1.0 400 Bad Request');
}
