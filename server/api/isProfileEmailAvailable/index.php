<?php

require_once "../../res/includes/main.php";

$profileEmail = $db->real_escape_string($req['profileEmail']);
$result = $db->query("SELECT profileEmail FROM profiles WHERE profileEmail = '$profileEmail'");

if ($result) {
    $res['status'] = true;
    if ($result->num_rows > 0) {
        $res['data']['available'] = false;
    } else {
        $res['data']['available'] = true;
    }
} else {
    $res['message'] = "ERROR: Query failed.";
}
respond();
