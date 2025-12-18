<?php
// api/due_soon_email.php

require_once __DIR__ . '/../config/db.php';
$mailConfig = require __DIR__ . '/../config/mail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

// Fetch tickets due within next 24 hours
$sql = "
SELECT
    t.id,
    t.ticket_code,
    t.full_name,
    t.email AS requestor_email,
    t.contract_type,
    t.due_date,

    l.name  AS reviewer_name,
    l.email AS reviewer_email

FROM tickets t
LEFT JOIN lawyers l ON l.email = t.assigned_lawyer

WHERE
    t.status <> 'Completed'
    AND t.due_date IS NOT NULL
    AND t.due_date <> '1970-01-01'
    AND t.email_24h_sent = 0
    AND t.due_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)

ORDER BY t.due_date ASC
";

$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    echo "No tickets due in 24 hours.\n";
    exit;
}

while ($row = $result->fetch_assoc()) {

    $mail = new PHPMailer(true);

    try {
        // SMTP config
        $mail->isSMTP();
        $mail->Host       = $mailConfig['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $mailConfig['username'];
        $mail->Password   = $mailConfig['password'];
        $mail->SMTPSecure = $mailConfig['encryption'];
        $mail->Port       = $mailConfig['port'];

        // From
        $mail->setFrom(
            $mailConfig['from_email'],
            $mailConfig['from_name']
        );

        // Reviewer
        if (!empty($row['reviewer_email'])) {
            $mail->addAddress(
                $row['reviewer_email'],
                $row['reviewer_name'] ?? 'Reviewer'
            );
        }

        // Requestor
        $mail->addCC(
            $row['requestor_email'],
            $row['full_name']
        );

        // Optional BCC
        if (!empty($mailConfig['bcc'])) {
            $mail->addBCC($mailConfig['bcc']);
        }

        // Email body
        $dueDate = date('M d, Y H:i', strtotime($row['due_date']));

       $ticketCode = htmlspecialchars($row['ticket_code']);
    $contract   = htmlspecialchars($row['contract_type']);
    $dueDate    = date('M d, Y h:i A', strtotime($row['due_date']));
    $requestor = htmlspecialchars($row['full_name']);

    $mail->isHTML(true);
    $mail->Subject = "Ticket {$ticketCode} Due Within 24 Hours";

    $mail->Body = "
    <!DOCTYPE html>
    <html>
    <head>
    <meta charset='UTF-8'>
    </head>
    <body style='margin:0;padding:0;background:#f4f6fb;font-family:Arial,Helvetica,sans-serif;'>

    <table width='100%' cellpadding='0' cellspacing='0'>
        <tr>
        <td align='center' style='padding:40px 0;'>

            <table width='600' cellpadding='0' cellspacing='0'
            style='background:#ffffff;border-radius:8px;overflow:hidden;
                    box-shadow:0 4px 12px rgba(0,0,0,0.08);'>

            <!-- Header -->
            <tr>
                <td style='background:#2f3192;padding:18px 24px;
                        color:#ffffff;font-size:18px;font-weight:600;'>
                Data Agreements &amp; Contracts
                </td>
            </tr>

            <!-- Content -->
            <tr>
                <td style='padding:24px;color:#1f2937;font-size:14px;line-height:1.6;'>

                <p style='margin-top:0;'>Good day,</p>

                <p>
                    This is a reminder that the following request is
                    <strong>due within 24 hours</strong>.
                </p>

                <!-- Ticket Card -->
                <div style='background:#f1f5ff;border:1px solid #dbe4ff;
                            border-radius:6px;padding:16px;margin:20px 0;'>
                    <div style='font-size:11px;color:#4f46e5;
                                letter-spacing:1px;margin-bottom:6px;'>
                    TICKET
                    </div>
                    <div style='font-size:20px;font-weight:700;'>
                    {$ticketCode}
                    </div>
                </div>

                <table width='100%' cellpadding='6' cellspacing='0'
                        style='font-size:13px;'>
                    <tr>
                    <td width='35%' style='color:#6b7280;'>Requester</td>
                    <td>{$requestor}</td>
                    </tr>
                    <tr>
                    <td style='color:#6b7280;'>Contract Type</td>
                    <td>{$contract}</td>
                    </tr>
                    <tr>
                    <td style='color:#6b7280;'>Due Date</td>
                    <td><strong>{$dueDate}</strong></td>
                    </tr>
                </table>

                <p style='margin-top:24px;'>
                    Please ensure this ticket is reviewed before the deadline.
                </p>

                <p style='margin-bottom:0;'>
                    <em>Data Agreements &amp; Contracts Portal</em>
                </p>

                </td>
            </tr>

            </table>

        </td>
        </tr>
    </table>

    </body>
    </html>
    ";

        $mail->send();

        // Mark email as sent
        $upd = $conn->prepare("
            UPDATE tickets
            SET email_24h_sent = 1,
                email_24h_sent_at = NOW()
            WHERE id = ?
        ");
        $upd->bind_param('i', $row['id']);
        $upd->execute();
        $upd->close();

        echo "Email sent for {$row['ticket_code']}\n";

    } catch (Exception $e) {
        error_log(
            "Email failed for {$row['ticket_code']}: " . $mail->ErrorInfo
        );
    }
}

$conn->close();
