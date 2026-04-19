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

$grip_section = "";

if (!empty($items_detail)) {
    $club_counter   = 1;
    $putter_counter = 1;

    $grip_section .= "
        <tr style='background:#f0f0f0;'>
            <td colspan='5' style='padding:12px;font-weight:700;font-size:14px;color:#1f2933;'>GRIP SELECTIONS</td>
        </tr>
        <tr style='background:#e8e8e8;'>
            <td style='padding:8px 12px;font-weight:700;font-size:12px;color:#555;'>Club</td>
            <td style='padding:8px 12px;font-weight:700;font-size:12px;color:#555;'>Grip</td>
            <td style='padding:8px 12px;font-weight:700;font-size:12px;color:#555;text-align:right;'>Grip Cost</td>
            <td style='padding:8px 12px;font-weight:700;font-size:12px;color:#555;text-align:right;'>Labor</td>
            <td style='padding:8px 12px;font-weight:700;font-size:12px;color:#555;text-align:right;'>Materials</td>
        </tr>";

    foreach ($items_detail as $index => $item) {
        $grip_name  = htmlspecialchars($item['brand'] . ' ' . $item['model'] . ' – ' . $item['size'] . ' – ' . $item['color']);
        $grip_price = number_format($item['grip_price'], 2);
        $labor      = number_format($item['labor_cost'], 2);
        $materials  = number_format($item['material_cost'], 2);

        if ($index < $clubs_num) {
            $slot_label = 'Club ' . $club_counter;
            $club_counter++;
        } else {
            $slot_label = 'Putter ' . $putter_counter;
            $putter_counter++;
        }

        $grip_section .= "
        <tr>
            <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#555;'>$slot_label</td>
            <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#1f2933;'>$grip_name</td>
            <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#1f2933;text-align:right;'>\$$grip_price</td>
            <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#1f2933;text-align:right;'>\$$labor</td>
            <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#1f2933;text-align:right;'>\$$materials</td>
        </tr>";
    }
} else {
    $total_clubs = $clubs_num + $putters_num;
    $labor_fmt   = number_format($labor_cost, 2);
    $mat_fmt     = number_format($material_cost, 2);

    $grip_section = "
        <tr style='background:#f0f0f0;'>
            <td colspan='3' style='padding:12px;font-weight:700;font-size:14px;color:#1f2933;'>LABOR &amp; MATERIALS</td>
        </tr>
        <tr style='background:#e8e8e8;'>
            <td style='padding:8px 12px;font-weight:700;font-size:12px;color:#555;'>Item</td>
            <td style='padding:8px 12px;font-weight:700;font-size:12px;color:#555;text-align:right;'>Per Club</td>
            <td style='padding:8px 12px;font-weight:700;font-size:12px;color:#555;text-align:right;'>× $total_clubs Clubs</td>
        </tr>
        <tr>
            <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#555;'>Labor</td>
            <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#1f2933;text-align:right;'>\$$labor_fmt</td>
            <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#1f2933;text-align:right;'>\$" . number_format($labor_cost * $total_clubs, 2) . "</td>
        </tr>
        <tr>
            <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#555;'>Gripping Materials</td>
            <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#1f2933;text-align:right;'>\$$mat_fmt</td>
            <td style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#1f2933;text-align:right;'>\$" . number_format($material_cost * $total_clubs, 2) . "</td>
        </tr>
        <tr>
            <td colspan='3' style='padding:8px 12px;color:#555;font-size:13px;'>Customer is providing their own grips.</td>
        </tr>";
}

function build_email($heading, $body_intro, $cust_name, $cust_email, $cust_phone, $cust_notes, $grip_section, $order_total) {
    return "
    <div style='font-family:Arial,sans-serif;max-width:640px;margin:0 auto;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;'>
        <div style='background:#1f2933;padding:24px 32px;text-align:center;'>
            <h1 style='color:white;margin:0;font-size:22px;'>Smith's Golf Grips</h1>
            <p style='color:gold;margin:6px 0 0;font-size:14px;'>$heading</p>
        </div>
        <div style='padding:24px 32px;background:#f9f9f9;'>
            <p style='color:#1f2933;font-size:15px;margin-top:0;'>$body_intro</p>
        </div>
        <table style='width:100%;border-collapse:collapse;'>
            <tr style='background:#f0f0f0;'>
                <td colspan='5' style='padding:12px;font-weight:700;font-size:14px;color:#1f2933;'>CUSTOMER INFO</td>
            </tr>
            <tr>
                <td colspan='2' style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#555;width:35%;'>Name</td>
                <td colspan='3' style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#1f2933;'>$cust_name</td>
            </tr>
            <tr>
                <td colspan='2' style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#555;'>Email</td>
                <td colspan='3' style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#1f2933;'>$cust_email</td>
            </tr>
            <tr>
                <td colspan='2' style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#555;'>Phone</td>
                <td colspan='3' style='padding:8px 12px;border-bottom:1px solid #e0e0e0;color:#1f2933;'>$cust_phone</td>
            </tr>
            $grip_section
        </table>
        <div style='padding:16px 32px;background:#f9f9f9;border-top:1px solid #e0e0e0;'>
            <p style='color:#555;font-size:13px;margin:0 0 8px;'><strong>Notes:</strong> " . ($cust_notes ?: "None") . "</p>
            <p style='color:#1f2933;font-size:16px;font-weight:700;margin:0;'>Estimated Total: \$" . number_format($order_total, 2) . "</p>
        </div>
        <div style='background:#1f2933;padding:16px 32px;text-align:center;'>
            <p style='color:#aaa;font-size:12px;margin:0;'>Smith's Golf Grips &mdash; Quality Regripping Service</p>
        </div>
    </div>";
}

$corey_body    = build_email("New Order Received", "A new order has been submitted. Details below.", $cust_name, $cust_email, $cust_phone, $cust_notes, $grip_section, $order_total);
$customer_body = build_email("Order Confirmation", "Hi $cust_name, thank you for your order! Here's a summary of what we received. We'll be in touch shortly to confirm your appointment.", $cust_name, $cust_email, $cust_phone, $cust_notes, $grip_section, $order_total);

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
