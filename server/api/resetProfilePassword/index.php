<?php

require_once "../../res/includes/main.php";

$profileEmail = $db->real_escape_string($req["profileEmail"]);
$newProfilePassword = password_hash($db->real_escape_string($req["newProfilePassword"]), PASSWORD_DEFAULT);

$result = $db->query("UPDATE profiles SET profilePassword = '$newProfilePassword' WHERE profileEmail = '$profileEmail'");

if ($result) {
    $res["status"] = true;
} else {
    $res["message"] = "ERROR: Query failed.";
}
respond();
