<?php

require_once "../../res/includes/main.php";

$profileUsername = $db->real_escape_string($req['profileUsername']);
$result = $db->query("SELECT profileUsername FROM profiles WHERE profileUsername = '$profileUsername'");
$result2 = $db->query("SELECT groupUsername FROM groups WHERE groupUsername = '$profileUsername'");


if ($result) {
    $res['status'] = true;
    $res['data']['available'] = true;
    if ($result->num_rows > 0) {
        $res['data']['available'] = false;
    }
    if ($result2->num_rows > 0) {
        $res['data']['available'] = false;
    }
} else {
    $res['message'] = "ERROR: Query failed.";
}
respond();
