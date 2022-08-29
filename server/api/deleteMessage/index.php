<?php

require_once "../../res/includes/main.php";

$messageID = $db->real_escape_string($req["messageID"]);
$res['status'] = true;

if (!$db->query("UPDATE `messages` SET `messageStatus` = 'Deleted' WHERE `messageID` = '$messageID'")) {
    error("Failed to delete message", $db->error);
}

respond();
