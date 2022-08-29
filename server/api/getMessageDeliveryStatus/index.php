<?php

require_once "../../res/includes/main.php";

$messageID = $db->real_escape_string($req["messageID"]);
$res["status"] = true;

$statuses = $db->query("SELECT * FROM `messagesdeliverystatus` WHERE `messageID` = '$messageID'");

if (!$statuses) {
    error("Failed to get message delivery statuses", $db->error);
}

$res["data"]["statuses"] = array();
$seen = true;
$delivered = true;
if ($statuses->num_rows > 0) {
    while ($status = $statuses->fetch_assoc()) {
        if ($status["messageStatus"] != "Seen") {
            $seen = false;
        } else if ($status["messageStatus"] != "Delivered") {
            $delivered = false;
        }
        $res["data"]["statuses"][] = $status;
    }
}

if ($seen) {
    $res["data"]["status"] = "Seen";
} else if ($delivered) {
    $res["data"]["status"] = "Delivered";
} else {
    $res["data"]["status"] = "Sent";
}
respond();
