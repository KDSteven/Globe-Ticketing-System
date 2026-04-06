<?php 
$servername="localhost";
$username="root";
$password="";
$database="globe";

$conn = new mysqli($servername,$username,$password,$database);

if($conn->connect_error) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('ok' =>  false, 'error' => 'Database connection failed'));
    exit;
}

$conn->set_charset("utf8mb4");

// Auto-apply CORS + general rate limit for all API requests
$_scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
if (str_contains($_scriptPath, '/api/')) {
    require_once __DIR__ . '/cors.php';
    require_once __DIR__ . '/rate_limit.php';
    apply_cors();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    rate_limit($conn, 'api_' . $ip, 120, 60);
}
unset($_scriptPath);
?>