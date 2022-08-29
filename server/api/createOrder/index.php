<?php

use Razorpay\Api\Api;

require_once "../../res/includes/main.php";
require_once "../../res/composer/vendor/autoload.php";

$amount = $db->real_escape_string($req["amount"]);

$api = new Api("rzp_test_W07spsmQp4SBSp", "ChSqgHGDNseqUXM0PwRA4sGf");

$res["data"]["orderID"] = $api->order->create(array('amount' => $amount, 'currency' => "INR"))["id"];
$res["status"] = true;
respond();
