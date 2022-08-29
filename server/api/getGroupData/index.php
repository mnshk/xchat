<?php

require_once "../../res/includes/main.php";

$activeProfileID = $db->real_escape_string($req["activeProfileID"]);
$otherProfileID = $db->real_escape_string($req["otherProfileID"]);

$res["status"] = true;

$result = $db->query("SELECT * FROM groups WHERE groupID = '$otherProfileID'");

if (!$result) {
    error("Failed to get group", $db->error);
}

if ($result->num_rows > 0) {
    $group = $result->fetch_assoc();
    if ($otherProfileID != "") {
        $isConnected = false;
        $con = $db->query("SELECT * FROM connections WHERE (activeProfileID = '$activeProfileID' AND otherProfileID = '$otherProfileID' AND `connectionType` = 'group') ");

        if (!$con) {
            error("Failed to get connection", $db->error);
        }

        if ($con->num_rows > 0 && $con->fetch_assoc()['connectionStatus'] == 'connected') {
            $isConnected = true;
        }
        if ($group["groupIconVisibility"] == "private") {
            if (!$isConnected) {
                $group["groupIconPath"] = "";
            }
        }
        if ($group["groupDescriptionVisibility"] == "private") {
            if (!$isConnected) {
                $group["groupDescription"] = "<i class='small text-secondary'>Connect to read description</i>";
            }
        }
    }
    if ($group["groupIconPath"]) {
        $group["groupIconPath"] = $groupIconURL . $group["groupIconPath"];
    }
    $res["data"]["groupData"] = $group;
}

respond();
