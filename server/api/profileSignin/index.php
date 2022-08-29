<?php

require_once "../../res/includes/main.php";

$profileUsername = $db->real_escape_string($req['profileUsername']);
$profilePassword = $db->real_escape_string($req['profilePassword']);

$result = $db->query("SELECT * FROM profiles WHERE profileUsername = '$profileUsername'");

if ($result) {
    $res["status"] = true;
    if ($result->num_rows > 0) {
        $profile = $result->fetch_assoc();
        if (password_verify($profilePassword, $profile['profilePassword'])) {
            $res["data"]["signinStatus"] = true;
            $profile["profileIconPath"] = $profileIconURL . $profile["profileIconPath"];
            unset($profile['profilePassword']);
            $profileID = $profile['profileID'];
            $db->query("UPDATE profiles SET profileEndpoint = '' WHERE profileID = '$profileID'");
            $res["data"]["userData"] = $profile;
        } else {
            $res["message"] = "ERROR: Incorrect password.";
        }
    } else {
        $res["message"] = "ERROR: No profile found.";
    }
} else {
    $res["message"] = "ERROR: Query failed.";
}
respond();
