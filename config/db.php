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
?>