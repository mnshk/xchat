<?php

require_once "../../res/includes/main.php";

$activeProfileID = $db->real_escape_string($req["activeProfileID"]);
$otherProfileID = $db->real_escape_string($req["otherProfileID"]);

$result = $db->query("SELECT * FROM profiles WHERE profileID = '$otherProfileID'");

if ($result) {
    $res["status"] = true;
    if ($result->num_rows > 0) {
        $profile = $result->fetch_assoc();
        unset($profile['profilePassword']);
        if ($otherProfileID != $activeProfileID) {
            $isConnected = false;
            $con = $db->query("SELECT * FROM connections WHERE (activeProfileID = '$activeProfileID' AND otherProfileID = '$otherProfileID' AND `connectionType` = 'profile') ");
            if ($con->num_rows > 0 && $con->fetch_assoc()['connectionStatus'] == 'connected') {
                $isConnected = true;
            }
            if ($profile["profileIconVisibility"] == "private") {
                if (!$isConnected) {
                    $profile["profileIconPath"] = "";
                }
            }
            if ($profile["profileDescriptionVisibility"] == "private") {
                if (!$isConnected) {
                    $profile["profileDescription"] = "<i class='small text-secondary'>Connect to read description</i>";
                }
            }
        }
        if ($profile["profileIconPath"]) {
            $profile["profileIconPath"] = $profileIconURL . $profile["profileIconPath"];
        }
        $res["data"]["userData"] = $profile;
    } else {
        $res["message"] = "ERROR: No profile found.";
    }
} else {
    $res["message"] = "ERROR: Query failed.";
}
respond();
