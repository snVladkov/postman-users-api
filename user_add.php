<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.' . $_SERVER['REQUEST_METHOD']]);
    exit;
}

if (empty($_POST)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or missing JSON body.']);
    exit;
}

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if ($name === '' || $email === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing name or email.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format.']);
    exit;
}

$storage = '/var/www/hacknatsait.com/api/users.json';
if (!file_exists($storage)) {
    if (!file_put_contents($storage, json_encode([]))) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to initialize storage.']);
        exit;
    }
}

$fp = fopen($storage, 'c+');
if (!$fp) {
    http_response_code(500);
    echo json_encode(['error' => 'Storage not writable.', 'fp' => var_dump($fp), 'storage' => $storage]);
    exit;
}
flock($fp, LOCK_EX);
$content = stream_get_contents($fp);
$users = json_decode($content, true) ?: [];

$maxId = 0;
foreach ($users as $u) {
    if (isset($u['id']) && is_numeric($u['id']) && (int)$u['id'] > $maxId) $maxId = (int)$u['id'];
}
$newId = $maxId + 1;

$newUser = [
    'id' => $newId,
    'name' => $name,
    'email' => $email,
    'created_at' => date('c')
];

$users[] = $newUser;
ftruncate($fp, 0);
rewind($fp);
fwrite($fp, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

http_response_code(201);
echo json_encode($newUser);
exit;
