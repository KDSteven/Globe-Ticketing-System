<?php
require __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

// Fetch ALL routing rules
$res = $conn->query("
    SELECT 
        rr.ticket_type,
        rr.cc_emails,
        l.name AS lawyer_name,
        l.email AS lawyer_email
    FROM routing_rules rr
    JOIN lawyers l ON rr.assigned_lawyer = l.id
    WHERE rr.active = 1
");

$final = [];

while ($r = $res->fetch_assoc()) {
    $final[$r['ticket_type']] = [
        "lawyer" => $r['lawyer_name'],
        "email"  => $r['lawyer_email'],
        "cc"     => $r['cc_emails'] ? explode(",", $r['cc_emails']) : []
    ];
}

echo json_encode($final);
