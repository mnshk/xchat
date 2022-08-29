<?php

use PHPMailer\PHPMailer\PHPMailer;

// require '../../res/composer/vendor/autoload.php';

function sendEmail($from, $to, $subject, $body)
{
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = '*******************';
        $mail->Password = '**********';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->isHTML(true);
        $mail->setFrom('*********', $from);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        echo $mail->ErrorInfo;
        return false;
    }
}
