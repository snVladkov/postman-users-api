<?php
// ME1Q8C enjoy the code :)
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$uptime = null;
if (is_readable('/proc/uptime')) {
    $parts = explode(' ', trim(file_get_contents('/proc/uptime')));
    $uptime = floatval($parts[0]);
}

$response = [
    'status' => 'ok',
    'time' => date('c'),
    'server_uptime_seconds' => $uptime
];

http_response_code(200);
echo json_encode($response);
exit;
