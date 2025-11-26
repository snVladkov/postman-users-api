<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$id = isset($_GET['id']) ? $_GET['id'] : null;
if ($id === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing id parameter']);
    exit;
}

$storage = '/var/www/hacknatsait.com/api/users.json';
if (!file_exists($storage)) {
    file_put_contents($storage, json_encode([]));
}
$users = json_decode(file_get_contents($storage), true) ?: [];

$found = null;
foreach ($users as $u) {
    if ((string)$u['id'] === (string)$id) {
        $found = $u;
        break;
    }
}

if ($found === null) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
} else {
    http_response_code(200);
    echo json_encode($found);
}
exit;
