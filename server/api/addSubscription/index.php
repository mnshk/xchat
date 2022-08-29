<?php

require_once "../../res/includes/main.php";

$profileID = $db->real_escape_string($req["profileID"]);
$subscriptionType = $db->real_escape_string($req["subscriptionType"]);
$subscriptionPeriod = $db->real_escape_string($req["subscriptionPeriod"]);

$paymentAmount = $db->real_escape_string($req["paymentAmount"]);
$paymentDescription = $db->real_escape_string($req["paymentDescription"]);
$orderID = $db->real_escape_string($req["orderID"]);

$currentDateTime = date("Y-m-d H:i:s");
$expiryDate = date("Y-m-d H:i:s", strtotime("+$subscriptionPeriod days"));

$res["status"] = true;

if (!$db->query("INSERT INTO subscriptions (profileID, subscriptionType, subscriptionStartedOn, subscriptionEndsOn) VALUES ('$profileID', '$subscriptionType', '$currentDateTime', '$expiryDate')")) {
    error("Failed to add subscription", $db->error);
}
$subscriptionID = $db->insert_id;

if (!$db->query("INSERT INTO payments (orderID, profileID, subscriptionID, paymentMadeOn, paymentAmount, paymentDescription) VALUES ('$orderID', '$profileID', '$subscriptionID', '$currentDateTime', '$paymentAmount', '$paymentDescription')")) {
    error("Failed to add payment", $db->error);
}

respond();
