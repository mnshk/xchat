<?php

require_once "../../res/includes/main.php";

$activeProfileID = $db->real_escape_string($req["activeProfileID"]);
$res["status"] = true;
$res["data"] = array();

$result = $db->query("SELECT * FROM connections WHERE (activeProfileID = '$activeProfileID' AND connectionType = 'group' AND connectionStatus = 'connected')");

if (!$result) {
    error("Failed to get connections", $db->error);
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $res["data"][] = $row;
    }
}

respond();
