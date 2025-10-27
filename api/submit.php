<?php
// api/submit.php — MySQLi version
include __DIR__ . '/../config/db.php';

function redirect_ok() {
    header('Location: /customer_portal.php?ok=1'); exit;
}
function redirect_err($msg) {
    header('Location: /customer_portal?error=' . urlencode($msg)); exit;
}

// Basic validation
$required = ['full_name','email','group','summary','contract_type','customer','vendor','pd_nature'];
foreach ($required as $key) {
    if (empty($_POST[$key])) redirect_err("Missing required field: $key");
}

// Collect data
$full_name       = trim($_POST['full_name']);
$email           = trim($_POST['email']);
$group           = trim($_POST['group']);
$tribe           = $_POST['tribe'] ?? null;
$assigned_lawyer = $_POST['assigned_lawyer'] ?? null;
$cc_emails       = $_POST['cc_emails'] ?? null;
$summary         = trim($_POST['summary']);
$contract_type   = trim($_POST['contract_type']);
$contract_other  = $_POST['contract_other'] ?? null;
$customer        = trim($_POST['customer']);
$vendor          = trim($_POST['vendor']);
$pd_nature       = trim($_POST['pd_nature']);
$pd_other_text   = $_POST['pd_other_text'] ?? null;
$clauses         = $_POST['clauses'] ?? ''; // 3.1 field
$doc_link        = $_POST['doc_link'] ?? null;

$ticket_code = 'GDA-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);

// Decide SLA days (adjust logic to your form fields)
$daysToAdd = 5; // default
// Example heuristic — refine as needed:
// if (isset($_POST['customer']) && trim($_POST['customer']) === 'Globe') {
//   $daysToAdd = 3;
// }

// ---- SLA / Due date helpers -----------------------------------------------
$createdAt = new DateTime('now', new DateTimeZone('Asia/Manila')); // or your TZ

function addBusinessDays(DateTime $date, int $days): DateTime {
  $d = clone $date;
  while ($days > 0) {
    $d->modify('+1 day');
    if ((int)$d->format('N') < 6) { // 1..5 = Mon..Fri
      $days--;
    }
  }
  return $d;
}

/**
 * Pick SLA days.
 * Adjust logic to your rules. Example below:
 *   - If customer is exactly "Globe" => 3 business days
 *   - Otherwise                      => 5 business days
 */
$daysToAdd = 5;
if (isset($customer) && trim(mb_strtolower($customer)) === 'globe') {
  $daysToAdd = 3;
}

$dueDate   = addBusinessDays($createdAt, $daysToAdd)->format('Y-m-d');        // DATE
$createdTS = $createdAt->format('Y-m-d H:i:s');                                // DATETIME
$priority  = $_POST['priority'] ?? 'Normal';                                   // Enum/Text
$status    = 'Pending';                                                        // default
// ---------------------------------------------------------------------------


// Insert ticket
$stmt = $conn->prepare("
  INSERT INTO tickets (
    ticket_code, full_name, email, grp, tribe,
    assigned_lawyer, cc_emails, summary, contract_type, contract_other,
    customer, vendor, pd_nature, pd_other_text, clauses, doc_link,
    created_at, priority, due_date, status
  )
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
  "ssssssssssssssssssss",  // 20 placeholders
  $ticket_code, $full_name, $email, $group, $tribe,
  $assigned_lawyer, $cc_emails, $summary, $contract_type, $contract_other,
  $customer, $vendor, $pd_nature, $pd_other_text, $clauses, $doc_link,
  $createdTS, $priority, $dueDate, $status
);

if (!$stmt->execute()) {
  redirect_err("DB insert failed: " . $stmt->error);
}

$ticket_id = $stmt->insert_id;
$stmt->close();


// Collect any full paths of uploaded files so we can attach them to the email
$savedFiles = [];  // [ ['path'=>'/full/path', 'name'=>'OriginalName.ext'], ... ]

$maxFiles = 5;
if (!empty($_FILES['attachments']['name'][0])) {
    $upload_dir = __DIR__ . '/../uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);

    $total = count($_FILES['attachments']['name']);
    for ($i = 0; $i < min($total, $maxFiles); $i++) {
        if ($_FILES['attachments']['error'][$i] !== UPLOAD_ERR_OK) continue;

        $orig = basename($_FILES['attachments']['name'][$i]);
        $tmp  = $_FILES['attachments']['tmp_name'][$i];
        $mime = $_FILES['attachments']['type'][$i];
        $size = $_FILES['attachments']['size'][$i];
        $ext  = pathinfo($orig, PATHINFO_EXTENSION);
        $save = $ticket_id . '_' . bin2hex(random_bytes(5)) . '.' . strtolower($ext);
        $dest = $upload_dir . $save;

        if (move_uploaded_file($tmp, $dest)) {
            // Save file row
            $f = $conn->prepare("
                INSERT INTO ticket_files (ticket_id, original, saved_as, mime, size_bytes)
                VALUES (?, ?, ?, ?, ?)
            ");
            $f->bind_param("isssi", $ticket_id, $orig, $save, $mime, $size);
            $f->execute();
            $f->close();

            // Remember for email attachments
            $savedFiles[] = ['path' => $dest, 'name' => $orig];
        }
    }
}

// -------- EMAIL SENDING (PHPMailer) ----------
$mailCfg = require __DIR__ . '/../config/mail.php';

// Helper to extract a single email from "Name <email@x>"
$extractEmail = function(string $s): string {
    if (preg_match('/<([^>]+)>/', $s, $m)) return trim($m[1]);
    // or if they typed just an email
    if (filter_var(trim($s), FILTER_VALIDATE_EMAIL)) return trim($s);
    return ''; // not found
};

// Assigned lawyer email (string like "Atty. Alex Austria <aaustria@...>")
$lawyerEmail = $extractEmail($assigned_lawyer);

// CC list: comma-separated in $cc_emails
$ccList = array_filter(array_map(function($x){
    return trim($x);
}, explode(',', (string)$cc_emails)));

require __DIR__ . '/../vendor/autoload.php'; // Composer autoload (or include PHPMailer classes manually)

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    // 1) Validate we actually have a lawyer email (routing issues)
    $lawyerEmail = $extractEmail($assigned_lawyer);
    if (!$lawyerEmail) {
        throw new Exception('No valid assigned lawyer email. Check group/tribe selection and routing logic.');
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $mailCfg['host'];
    $mail->Port       = $mailCfg['port'];
    $mail->SMTPSecure = $mailCfg['encryption'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $mailCfg['username'];
    $mail->Password   = $mailCfg['password'];

    // Recommended for local/XAMPP if you hit certificate issues
    $mail->SMTPOptions = [
      'ssl' => [
        'verify_peer'       => false,
        'verify_peer_name'  => false,
        'allow_self_signed' => true
      ]
    ];


    $mail->CharSet  = 'UTF-8';
    $mail->Encoding = 'base64';

    // From / To / CC
    $mail->setFrom($mailCfg['from_email'], $mailCfg['from_name']);

    // IMPORTANT for Gmail: from_email MUST equal username (or a verified alias)
    if (strcasecmp($mailCfg['from_email'], $mailCfg['username']) !== 0) {
        throw new Exception('For Gmail, from_email must equal username (or be a verified alias).');
    }

    $mail->addAddress($lawyerEmail);

    // Robust CC parsing (commas/semicolons, strip “< >”)
    $ccList = preg_split('/[;,]+/', (string)$cc_emails);
    $ccList = array_filter(array_map(function ($x) {
        $x = trim($x);
        if (preg_match('/<([^>]+)>/', $x, $m)) $x = $m[1];
        return filter_var($x, FILTER_VALIDATE_EMAIL) ? $x : '';
    }, $ccList));
    foreach ($ccList as $one) $mail->addCC($one);

    if (!empty($mailCfg['bcc'])) {
        foreach (preg_split('/[;,]+/', $mailCfg['bcc']) as $bcc) {
            $bcc = trim($bcc);
            if (filter_var($bcc, FILTER_VALIDATE_EMAIL)) $mail->addBCC($bcc);
        }
    }

    // Reply-To: requester
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mail->addReplyTo($email, $full_name);
    }

    $subject = sprintf('[%s] New submission from %s', $ticket_code, $full_name);
    $mail->Subject = $subject;

// ---------- Build a clean HTML email (HEREDOC-safe) ----------

// Simple escaper
$esc = function($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };

// Names / displays (no expressions inside HEREDOC later)
$lawyerName     = trim(preg_replace('/<.*?>/','', (string)$assigned_lawyer)) ?: 'Counsel';
$lawyerNameEsc  = $esc($lawyerName);
$ticketCodeEsc  = $esc($ticket_code);
$fullNameEsc    = $esc($full_name);
$emailEsc       = $esc($email);
$groupEsc       = $esc($group);
$tribeEsc       = $tribe !== '' ? $esc($tribe) : '';
$assignedLawyerEsc = $assigned_lawyer !== '' ? $esc($assigned_lawyer) : '—';
$ccEsc          = $cc_emails !== '' ? $esc($cc_emails) : '—';
$summaryEsc     = $summary !== '' ? $esc($summary) : '—';
$clausesEsc     = $clauses !== '' ? $esc($clauses) : '—';
$customerEsc    = $customer !== '' ? $esc($customer) : '—';
$vendorEsc      = $vendor !== '' ? $esc($vendor) : '—';

// Pretty strings prepared outside the HEREDOC
$contractTypePretty = ($contract_type === 'OTHER')
    ? 'Other: '.$esc($contract_other)
    : $esc($contract_type);

$pdNaturePretty = ($pd_nature === 'OTHER')
    ? 'Other: '.$esc($pd_other_text)
    : $esc($pd_nature);

$groupPretty = $tribeEsc !== ''
    ? $groupEsc . ' · <em>' . $tribeEsc . '</em>'
    : $groupEsc;

if ($doc_link && strtoupper(trim($doc_link)) !== 'N/A') {
    $docDisplay = '<a href="' . $esc($doc_link) . '" target="_blank" rel="noopener">' . $esc($doc_link) . '</a>';
} else {
    $docDisplay = 'N/A';
}

// Subject
$mail->Subject = sprintf('[%s] New submission from %s', $ticket_code, $full_name);

// Plain-text fallback
$altLines = [
  "Good day {$lawyerName}, a new request has been submitted by {$full_name}.",
  "",
  "Ticket Code: {$ticket_code}",
  "Requester Email: {$email}",
  "Group: {$group}" . ($tribe ? " / Tribe: {$tribe}" : ""),
  "Assigned Lawyer: " . ($assigned_lawyer ?: '—'),
  "CC/Loop Emails: " . ($cc_emails ?: '—'),
  "",
  "— Summary —",
  trim($summary) ?: '—',
  "",
  "— Contract Type —",
  $contractTypePretty,
  "",
  "Customer: " . ($customer ?: '—'),
  "Vendor: "   . ($vendor   ?: '—'),
  "",
  "— PD Nature —",
  $pdNaturePretty,
  "",
  "— Specific Clauses to Review —",
  trim($clauses) ?: '—',
  "",
  "Google Doc/Sheet Link: " . ($doc_link ?: 'N/A'),
  "",
  "Reply to this email to keep the conversation in one thread."
];
$mail->AltBody = implode("\r\n", $altLines);

// HTML body (variables only; no function calls/expressions)
$html = <<<HTML
<!doctype html>
<html>
  <body style="margin:0;background:#f6f7fb;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#111;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f7fb;padding:24px 0;">
      <tr>
        <td align="center">
          <table role="presentation" width="620" cellspacing="0" cellpadding="0" style="background:#ffffff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.05);overflow:hidden">
            <tr>
              <td style="background:#2E3192;color:#fff;padding:18px 24px;font-weight:700;font-size:18px">
                Data Agreements &amp; Contracts — New Request
              </td>
            </tr>
            <tr>
              <td style="padding:20px 24px;font-size:14px;line-height:1.6">
                <p style="margin:0 0 12px;">Good day {$lawyerNameEsc},</p>
                <p style="margin:0 0 18px;">A new request has been submitted by <strong>{$fullNameEsc}</strong>. Details are below.</p>

                <div style="margin:12px 0 18px;padding:12px 16px;background:#f2f5ff;border:1px solid #e1e6ff;border-radius:8px">
                  <div style="font-size:12px;color:#2E3192;letter-spacing:.3px;text-transform:uppercase;">Ticket</div>
                  <div style="font-size:22px;font-weight:800;letter-spacing:.4px;">{$ticketCodeEsc}</div>
                </div>

                <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;font-size:14px;">
                  <tr>
                    <td style="padding:6px 0;width:170px;color:#666;">Requester Email</td>
                    <td style="padding:6px 0;"><a href="mailto:{$emailEsc}">{$emailEsc}</a></td>
                  </tr>
                  <tr>
                    <td style="padding:6px 0;color:#666;">Group</td>
                    <td style="padding:6px 0;">{$groupPretty}</td>
                  </tr>
                  <tr>
                    <td style="padding:6px 0;color:#666;">Assigned Lawyer</td>
                    <td style="padding:6px 0;">{$assignedLawyerEsc}</td>
                  </tr>
                  <tr>
                    <td style="padding:6px 0;color:#666;">CC/Loop</td>
                    <td style="padding:6px 0;">{$ccEsc}</td>
                  </tr>
                  <tr>
                    <td style="padding:6px 0;color:#666;">Contract Type</td>
                    <td style="padding:6px 0;">{$contractTypePretty}</td>
                  </tr>
                  <tr>
                    <td style="padding:6px 0;color:#666;">Customer</td>
                    <td style="padding:6px 0;">{$customerEsc}</td>
                  </tr>
                  <tr>
                    <td style="padding:6px 0;color:#666;">Vendor</td>
                    <td style="padding:6px 0;">{$vendorEsc}</td>
                  </tr>
                  <tr>
                    <td style="padding:6px 0;color:#666;">PD Nature</td>
                    <td style="padding:6px 0;">{$pdNaturePretty}</td>
                  </tr>
                  <tr>
                    <td style="padding:6px 0;color:#666;">Google Doc/Sheet</td>
                    <td style="padding:6px 0;">{$docDisplay}</td>
                  </tr>
                </table>

                <hr style="border:none;border-top:1px solid #eee;margin:18px 0">

                <p style="margin:0 0 6px;color:#666;font-weight:700;">Summary</p>
                <p style="white-space:pre-wrap;margin:0 0 14px;">{$summaryEsc}</p>

                <p style="margin:14px 0 6px;color:#666;font-weight:700;">Specific Clauses to Review</p>
                <p style="white-space:pre-wrap;margin:0;">{$clausesEsc}</p>

                <p style="margin:18px 0 0;color:#666;">You can reply directly to this email to keep the discussion in a single thread.</p>
              </td>
            </tr>
            <tr>
              <td style="background:#f6f7fb;color:#777;font-size:12px;padding:14px 24px;text-align:center">
                Sent by the Data Agreements &amp; Contracts Portal
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>
HTML;

$mail->isHTML(true);
$mail->Body = $html;


    // Attachments (skip >20MB to avoid SMTP failure)
    foreach ($savedFiles as $f) {
        if (@filesize($f['path']) > 20*1024*1024) continue;
        $mail->addAttachment($f['path'], $f['name']);
    }

    $mail->send();

    header('Location: /index.php?ok=1'); // or /portal.php?ok=1 if that’s your default
    exit;

} catch (Exception $e) {
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo '<div style="padding:12px;border:1px solid #ccc;background:#fff3f3;color:#900;margin:10px 0">';
    echo '<strong>Mailer error:</strong> ' . htmlentities($e->getMessage());
    echo '</div>';
    // Also log the native PHPMailer info (if any was set)
    // error_log('Mailer error on ticket '.$ticket_id.': '.$e->getMessage());
    exit; // keep the error visible
}

// Handle uploads (unchanged)
$maxFiles = 5;
if (!empty($_FILES['attachments']['name'][0])) {
  $upload_dir = __DIR__ . '/../uploads/';
  if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);

  $total = count($_FILES['attachments']['name']);
  for ($i = 0; $i < min($total, $maxFiles); $i++) {
    if ($_FILES['attachments']['error'][$i] !== UPLOAD_ERR_OK) continue;

    $orig = basename($_FILES['attachments']['name'][$i]);
    $tmp  = $_FILES['attachments']['tmp_name'][$i];
    $mime = $_FILES['attachments']['type'][$i];
    $size = $_FILES['attachments']['size'][$i];
    $ext  = pathinfo($orig, PATHINFO_EXTENSION);
    $save = $ticket_id . '_' . bin2hex(random_bytes(5)) . '.' . strtolower($ext);
    $dest = $upload_dir . $save;

    if (move_uploaded_file($tmp, $dest)) {
      $f = $conn->prepare("
        INSERT INTO ticket_files (ticket_id, original, saved_as, mime, size_bytes)
        VALUES (?, ?, ?, ?, ?)
      ");
      $f->bind_param("isssi", $ticket_id, $orig, $save, $mime, $size);
      $f->execute();
      $f->close();
    }
  }
}

$conn->close();
redirect_ok();
