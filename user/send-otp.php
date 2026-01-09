<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

function sendOTP($toEmail, $otp) {

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'vengalamadasamy.ctf@gmail.com';     // 🔴 your email
        $mail->Password   = 'azmrrjheioeigciv';            // 🔴 Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('kiddiyspoon@info.com', 'Kiddy Spoon');
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Kiddy Spoon - Email Verification OTP';
        $mail->Body = "
            <h2>Email Verification</h2>
            <p>Your OTP is:</p>
            <h1>$otp</h1>
            <p>Do not share this OTP.</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}
