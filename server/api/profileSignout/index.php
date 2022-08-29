<?php

require_once "../../res/includes/main.php";

$profileID = $db->real_escape_string($req["profileID"]);
$res["status"] = true;
if (!$db->query("UPDATE `profiles` SET `profileEndpoint` = NULL WHERE `profileID` = '$profileID'")) {
    error("Failed to clear endpoint", $db->error);
}
respond();
