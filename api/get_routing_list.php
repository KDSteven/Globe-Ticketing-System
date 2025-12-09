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

$dropdown = [
    "Commercial Groups" => [
        "B2B",
        "Broadband Business (BB)",
        "Enterprise Data and Strategic Services (EDS)",
        "Product Engineering and Digital Growth (PEDG)",
        "Consumer Mobile Business (CMB)",
        "Channel Management (CMG)",
        "Marketing (MKT)",
        "Office of the Chief Commercial Officer (CCO)"
    ],
    "Other Groups" => [
        "Network Technical Group (NTG)",
        "Information Services Group (ISG)",
        "Corporate and Legal Services Group (CLSG)",
        "Corporate Communications (CorpComm)",
        "Information Security and Data Privacy (ISDP)",
        "Internal Controls (ICG)",
        "ST Telemedia (STT)",
        "Office of Strategy Mgmt & Customer Experience (OSMCX)",
        "Finance & Administration (FBA)",
        "Human Resources (HR)"
    ]
];
echo json_encode([
    "routing" => $final,
    "dropdown" => $dropdown
]);
