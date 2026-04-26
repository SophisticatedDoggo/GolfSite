<?php
require 'config.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'hcaptcha.php';

$token = $_POST['h-captcha-response'] ?? '';
if (!verifyHcaptcha($token, $_SERVER['REMOTE_ADDR'])) {
    header('Location: index.html?status=error#contact');
    exit();
}

$name = htmlspecialchars($_POST['name']);
$email = htmlspecialchars($_POST['email']);
$phone = htmlspecialchars($_POST['phone']);
$message = htmlspecialchars($_POST['message']);

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom(MAIL_USERNAME, "Smith's Golf Grips");
    $mail->addReplyTo($email, $name);
    $mail->addAddress(MAIL_TO);

    $mail->Subject = 'New Regrip Request from ' . $name;
    $mail->Body    = "Name: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message";

    $mail->send();
    header('Location: index.html?status=success#contact');
}   catch (Exception $e) {
    header('Location: index.html?status=error#contact');
}