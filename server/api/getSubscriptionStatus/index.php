<?php

require_once "../../res/includes/main.php";

$profileID = $db->real_escape_string($req["activeProfileID"]);
$currentDateTime = date("Y-m-d H:i:s");

$result = $db->query("SELECT * FROM subscriptions WHERE (profileID = '$profileID' AND subscriptionEndsOn > '$currentDateTime')");

// check if trial is expired or not

$result2 = $db->query("SELECT * FROM subscriptions WHERE (profileID = '$profileID' AND subscriptionType = 'trial' AND subscriptionEndsOn < '$currentDateTime')");

$res["status"] = true;

if ($result) {
    if ($result->num_rows > 0) {
        $res["data"] = array(
            "subscribed" => true,
        );
    } else {
        $res["data"] = array(
            "subscribed" => false,
        );
    }
} else {
    $res["status"] = false;
    $res["message"] = "Failed to get subscription status";
}

if ($result2) {
    if ($result2->num_rows > 0) {
        $res["data"]["trialExpired"] = true;
    } else {
        $res["data"]["trialExpired"] = false;
    }
} else {
    $res["status"] = false;
    $res["message"] = "Failed to get subscription status";
}

respond();
