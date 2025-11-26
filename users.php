<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$storage = '/var/www/hacknatsait.com/api/users.json';
if (!file_exists($storage)) {
    file_put_contents($storage, json_encode([]));
}
$contents = file_get_contents($storage);
$users = json_decode($contents, true);
if ($users === null) $users = [];

http_response_code(200);
echo json_encode($users);
exit;
