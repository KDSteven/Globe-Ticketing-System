<?php
require __DIR__ . '/../config/db.php';

header("Content-Type: application/json");

$code = $_GET['code'] ?? '';

if ($code === '') {
    echo json_encode(["error" => "Missing ticket code"]);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        full_name,
        email,
        grp,          -- correct column
        tribe,
        summary,
        customer,
        vendor,
        contract_type,
        pd_nature,
        pd_other_text,
        clauses,
        doc_link
    FROM tickets
    WHERE ticket_code = ?
    LIMIT 1
");
$stmt->bind_param("s", $code);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["error" => "Ticket not found"]);
    exit;
}

$row = $res->fetch_assoc();

// Return valid JSON
echo json_encode($row);
