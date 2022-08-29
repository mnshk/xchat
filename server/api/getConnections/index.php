<?php

require_once "../../res/includes/main.php";

$activeProfileID = $db->real_escape_string($req["activeProfileID"]);
$connectionType = $db->real_escape_string($req["connectionType"]);

$res["status"] = true;
$res['data']['connections'] = array();

$connections = $db->query("SELECT * FROM `connections` WHERE (`activeProfileID` = '$activeProfileID' AND `connectionType` = '$connectionType')");

if (!$connections) {
    error("Failed to get connections", $db->error);
}

if ($connections->num_rows > 0) {
    while ($connection = $connections->fetch_assoc()) {
        $res['data']['connections'][] = $connection;
    }
}

respond();
