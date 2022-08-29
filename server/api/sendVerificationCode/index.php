<?php

require_once "../../res/includes/main.php";

$profileEmail = $db->real_escape_string($req['profileEmail']);
// $verificationCode = mt_rand(100000, 999999);
$verificationCode = 999999;

$subject = "Verify your email address";
$from = "XCHAT Email Confirmation";
$body = "Your verification code is: $verificationCode";

require_once('../../res/includes/sendEmail.php');

// if (sendEmail($from, $profileEmail, $subject, $body)) {
if (1) {
    $res['status'] = true;
    $res['data'] = array(
        "verificationCode" => $verificationCode,
    );
} else {
    error("Failed to send verification code.");
}
respond();
