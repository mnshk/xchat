<?php

require_once "../../res/includes/main.php";

$profileID = $db->real_escape_string($req["activeProfileID"]);

$result = $db->query("SELECT * FROM subscriptions WHERE profileID = '$profileID' ORDER BY subscriptionStartedOn DESC");
$res["status"] = true;

if ($result) {
    $res["data"]["subscriptions"] = array();
    $res["data"]["payments"] = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $res["data"]["subscriptions"][] = $row;
            $subscriptionID = $row["subscriptionID"];
            $result2 = $db->query("SELECT * FROM payments WHERE subscriptionID = '$subscriptionID' ORDER BY paymentMadeOn DESC LIMIT 1");
            if ($result2) {
                if ($result2->num_rows > 0) {
                    $res["data"]["payments"][] = $result2->fetch_assoc();
                }
            }
        }
    }
} else {
    $res["status"] = false;
    $res["message"] = "Failed to get subscriptions";
}

respond();
