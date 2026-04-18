<?php
require '../config.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';
require '../phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$cust_name   = htmlspecialchars($_POST['cust_name']);
$cust_email  = htmlspecialchars($_POST['cust_email']);
$cust_phone  = htmlspecialchars($_POST['cust_phone']);
$cust_notes  = htmlspecialchars($_POST['cust_notes']);
$clubs_num   = (int) $_POST['clubs_num'];
$putters_num = (int) $_POST['putters_num'];

$club_rows = "";
for ($i = 1; $i <= $clubs_num; $i++) {
    if (isset($_POST['club_grip_' . $i]) && $_POST['club_grip_' . $i] !== '') {
        $grip = htmlspecialchars($_POST['club_grip_' . $i]);
        $club_rows .= "
                <tr>
                    <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#555;'>Club $i</td>
                    <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#1f2933;'>$grip</td>
                </tr>";
    }
}

$putter_rows = "";
for ($i = 1; $i <= $putters_num; $i++) {
    if (isset($_POST['putter_grip_' . $i]) && $_POST['putter_grip_' . $i] !== '') {
        $grip = htmlspecialchars($_POST['putter_grip_' . $i]);
        $putter_rows .= "
                <tr>
                    <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#555;'>Putter $i</td>
                    <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#1f2933;'>$grip</td>
                </tr>";
    }
}

$grip_section = "";
if ($club_rows) {
    $grip_section .= "
        <tr><td colspan='2' style='padding:12px;background:#2e5e3e;color:white;font-weight:700;font-size:14px;'>CLUB GRIPS</td></tr>
        $club_rows";
}
if ($putter_rows) {
    $grip_section .= "
        <tr><td colspan='2' style='padding:12px;background:#2e5e3e;color:white;font-weight:700;font-size:14px;'>PUTTER GRIPS</td></tr>
        $putter_rows";
}
if (!$club_rows && !$putter_rows) {
    $grip_section = "<tr><td colspan='2' style='padding:12px;color:#555;'>Customer is providing their own grips.</td></tr>";
}

function build_email($heading, $body_intro, $cust_name, $cust_email, $cust_phone, $cust_notes, $grip_section) {
    return "
    <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;'>
        <div style='background:#1f2933;padding:24px 32px;text-align:center;'>
            <h1 style='color:white;margin:0;font-size:22px;'>Smith's Golf Grips</h1>
            <p style='color:gold;margin:6px 0 0;font-size:14px;'>$heading</p>
        </div>
        <div style='padding:24px 32px;background:#f9f9f9;'>
            <p style='color:#1f2933;font-size:15px;margin-top:0;'>$body_intro</p>
        </div>
        <table style='width:100%;border-collapse:collapse;'>
            <tr style='background:#f0f0f0;'>
                <td colspan='2' style='padding:12px;font-weight:700;font-size:14px;color:#1f2933;'>CUSTOMER INFO</td>
            </tr>
            <tr>
                <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#555;width:35%;'>Name</td>
                <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#1f2933;'>$cust_name</td>
            </tr>
            <tr>
                <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#555;'>Email</td>
                <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#1f2933;'>$cust_email</td>
            </tr>
            <tr>
                <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#555;'>Phone</td>
                <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#1f2933;'>$cust_phone</td>
            </tr>
            <tr style='background:#f0f0f0;'>
                <td colspan='2' style='padding:12px;font-weight:700;font-size:14px;color:#1f2933;'>GRIP SELECTIONS</td>
            </tr>
            $grip_section
        </table>
        <div style='padding:16px 32px;background:#f9f9f9;border-top:1px solid #e0e0e0;'>
            <p style='color:#555;font-size:13px;margin:0;'><strong>Notes:</strong> " . ($cust_notes ?: "None") . "</p>
        </div>
        <div style='background:#1f2933;padding:16px 32px;text-align:center;'>
            <p style='color:#aaa;font-size:12px;margin:0;'>Smith's Golf Grips &mdash; Quality Regripping Service</p>
        </div>
    </div>";
}

$corey_body    = build_email("New Order Received", "A new order has been submitted. Details below.", $cust_name, $cust_email, $cust_phone, $cust_notes, $grip_section);
$customer_body = build_email("Order Confirmation", "Hi $cust_name, thank you for your order! Here's a summary of what we received. We'll be in touch shortly to confirm your appointment.", $cust_name, $cust_email, $cust_phone, $cust_notes, $grip_section);

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->isHTML(true);
    $mail->setFrom(MAIL_USERNAME, "Smith's Golf Grips");

    // Email to Corey
    $mail->addAddress(MAIL_TO);
    $mail->Subject = "New Order from $cust_name";
    $mail->Body    = $corey_body;
    $mail->send();

    // Email to customer
    $mail->clearAddresses();
    $mail->addAddress($cust_email, $cust_name);
    $mail->addReplyTo(MAIL_TO, "Smith's Golf Grips");
    $mail->Subject = "Your Order with Smith's Golf Grips";
    $mail->Body    = $customer_body;
    $mail->send();

} catch (Exception $e) {
    // silent fail — order is already saved to DB
}
