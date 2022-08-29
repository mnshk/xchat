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
        $mail->Username = 'web.munish.ml@gmail.com';
        $mail->Password = 'imasmclydhfrwvkw';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->isHTML(true);
        $mail->setFrom('web.munish.ml@gmail.com', $from);
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
