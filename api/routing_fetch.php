<?php
require __DIR__ . '/../config/db.php';

// Load static config
$static = require __DIR__ . '/../config/routing_rules.php';

// Extract lawyer profiles
$lawyerProfiles = $static["_LAWYERS"];
unset($static["_LAWYERS"]); // remove internal metadata array


/* -----------------------------------------------
   FETCH DB OVERRIDES
------------------------------------------------*/
$rows = $conn->query("
    SELECT 
        rr.ticket_type,
        rr.active,
        rr.assigned_lawyer,
        rr.cc_emails,
        l.name AS lawyer_name,
        l.email AS lawyer_email
    FROM routing_rules rr
    JOIN lawyers l ON rr.assigned_lawyer = l.id
    WHERE rr.active = 1
");

$dbRules = [];

while ($r = $rows->fetch_assoc()) {

    // Build CC array from DB field
    $cc = [];
    if (!empty($r['cc_emails'])) {
        $cc = array_filter(array_map("trim", explode(",", $r['cc_emails'])));
    }

    // Store DB override
    $dbRules[$r['ticket_type']] = [
        "lawyer" => $r['lawyer_name'],
        "email"  => $r['lawyer_email'],
        "cc"     => $cc
    ];
}


/* -----------------------------------------------
   FINAL MERGE: DB overrides static config
------------------------------------------------*/
$final = [];

// 1. Build entries for all static-defined ticket types
foreach ($static as $ticketType => $lawyerKey) {

    // If DB override exists → use DB version
    if (isset($dbRules[$ticketType])) {
        $final[$ticketType] = $dbRules[$ticketType];
        continue;
    }

    // If static references a lawyer profile
    if (isset($lawyerProfiles[$lawyerKey])) {
        $profile = $lawyerProfiles[$lawyerKey];

        $final[$ticketType] = [
            "lawyer" => $profile["name"],
            "email"  => $profile["email"],
            "cc"     => $profile["cc"] ?? []
        ];
    }
}

// 2. Add DB-only ticket types (not in static file)
foreach ($dbRules as $ticketType => $rule) {
    if (!isset($final[$ticketType])) {
        $final[$ticketType] = $rule;
    }
}

// Output JSON
header("Content-Type: application/json");
echo json_encode($final);
