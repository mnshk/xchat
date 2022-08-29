<?php

require_once "../../res/includes/main.php";

$profileID = $db->real_escape_string($req['profileID']);
$profileType = $db->real_escape_string($req['profileType']);
$res["status"] = true;

if (!$db->query("UPDATE `" . $profileType . "s` SET `" . $profileType . "IconPath` = '' WHERE `" . $profileType . "ID` = '$profileID'")) {
    $res["message"] = "ERROR: Query failed." . $db->error;
    $res["status"] = false;
}

respond();
