<?php
include __DIR__ . '/connection.php';

use PHPMailer\PHPMailer\PHPMailer;

require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';
require __DIR__ . '/../PHPMailer/src/Exception.php';

$limit = 20;

$stmt = $conn->prepare("
    SELECT id, email, full_name, amount, level
    FROM email_queue
    WHERE status='pending'
    LIMIT ?
");
$stmt->bind_param("i", $limit);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'mail.makethub.shop';
        $mail->SMTPAuth = true;
        $mail->Username = 'no-reply@makethub.shop';
        $mail->Password = '1745Sjm*1745';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('no-reply@makethub.shop', 'Makethub');
        $mail->addAddress($row['email'], $row['full_name']);

        $mail->Subject = "Level {$row['level']} Earnings";

        $mail->Body = "Hello {$row['full_name']},\n\n"
            . "Hooray 🥳! You've just earned KES " . number_format($row['amount'], 2) . ".\n"
            . "Keep growing your network.\n\n- Makethub 😊";

        $mail->send();

        // ✅ Mark as sent
        $update = $conn->prepare("
            UPDATE email_queue 
            SET status='sent', sent_at=NOW() 
            WHERE id=?
        ");
        $update->bind_param("i", $row['id']);
        $update->execute();
        $update->close();

    } catch (Exception $e) {

        // ❌ Mark as failed + increment attempts
        $update = $conn->prepare("
            UPDATE email_queue 
            SET status='failed', attempts = attempts + 1 
            WHERE id=?
        ");
        $update->bind_param("i", $row['id']);
        $update->execute();
        $update->close();
    }
}
?>