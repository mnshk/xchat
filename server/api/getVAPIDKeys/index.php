<?php

require_once "../../res/includes/main.php";

$result = $db->query("SELECT publicKey FROM vapidkeys");

if ($result) {
    if ($result->num_rows > 0) {
        $res["status"] = true;
        $row = $result->fetch_assoc();
        $res["data"] = array(
            "publicKey" => $row["publicKey"]
        );
    } else {
        $res["message"] = "No vapid keys found";
    }
} else {
    $res["message"] = "Error: " . $db->error;
}
respond();
