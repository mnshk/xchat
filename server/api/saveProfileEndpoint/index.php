<?php

require_once "../../res/includes/main.php";

$profileID = $db->real_escape_string($req["profileID"]);
$endpoint = $db->real_escape_string($req["endpoint"]);
$res["status"] = true;

if (!$db->query("UPDATE profiles SET profileEndpoint = '$endpoint' WHERE profileID = $profileID")) {
    error("ERROR: Failed to update profile endpoint.", $db->error);
}

respond();
