<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
    exit;
}

if (empty($_POST)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or missing JSON body.']);
    exit;
}

$id = isset($_POST['id']) ? $_POST['id'] : null;
if ($id === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing id.']);
    exit;
}

$storage = '/var/www/hacknatsait.com/api/users.json';
if (!file_exists($storage)) file_put_contents($storage, json_encode([]));

$fp = fopen($storage, 'c+');
if (!$fp) {
    http_response_code(500);
    echo json_encode(['error' => 'Storage not writable.']);
    exit;
}
flock($fp, LOCK_EX);
$content = stream_get_contents($fp);
$users = json_decode($content, true) ?: [];

$foundIndex = null;
foreach ($users as $i => $u) {
    if ((string)$u['id'] === (string)$id) {
        $foundIndex = $i;
        break;
    }
}
if ($foundIndex === null) {
    flock($fp, LOCK_UN);
    fclose($fp);
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}

// Полета, които разрешаваме да се ъпдейтват:
if (isset($_POST['name'])) $users[$foundIndex]['name'] = trim($_POST['name']);
if (isset($_POST['email'])) {
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        flock($fp, LOCK_UN);
        fclose($fp);
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email format.']);
        exit;
    }
    $users[$foundIndex]['email'] = trim($_POST['email']);
}
$users[$foundIndex]['updated_at'] = date('c');

ftruncate($fp, 0);
rewind($fp);
fwrite($fp, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

http_response_code(200);
echo json_encode($users[$foundIndex]);
exit;
