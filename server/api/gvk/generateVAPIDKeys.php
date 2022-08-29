<?php

use Minishlink\WebPush\VAPID;

require_once "../../res/includes/main.php";
require_once "../../res/composer/vendor/autoload.php";

header('Content-Type: text/plain');

$res["status"] = true;

$result = $db->query("SELECT * FROM `vapidkeys`");

if (!$result) {
    error("Database query failed", $db->error);
}

if ($result->num_rows > 0) {
    $res["message"] = "VAPID keys already generated";
    $row = $result->fetch_assoc();
    $publicKey = $row["publicKey"];
    $privateKey = $row["privateKey"];
} else {
    $res["message"] = "Generating VAPID keys";
    $VAPID = VAPID::createVAPIDKeys();
    $publicKey = $VAPID["publicKey"];
    $privateKey = $VAPID["privateKey"];
    if (!$db->query("INSERT INTO `vapidkeys` (`publicKey`, `privateKey`) VALUES ('$publicKey', '$privateKey')")) {
        error("Database query failed inserting", $db->error);
    }
}
$res["data"] = array("publicKey" => $publicKey, "privateKey" => $privateKey);
respond();
