<?php

require_once "../../res/includes/main.php";

$activeProfileID = $db->real_escape_string($req["activeProfileID"]);
$res["status"] = true;
$res['data']['connections'] = array();

$result = $db->query("SELECT groupID FROM `groups` WHERE (`groupOwnerProfileID` = '$activeProfileID')");

if (!$result) {
    error("Failed to get groups", $db->error);
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $groupID = $row['groupID'];
        $result2 = $db->query("SELECT * FROM `connections` WHERE (`activeProfileID` = '$groupID' AND `connectionType` = 'group')");
        if (!$result2) {
            error("Failed to get connections", $db->error);
        }
        if ($result2->num_rows > 0) {
            while ($row2 = $result2->fetch_assoc()) {
                $res['data']['connections'][] = $row2;
            }
        }
    }
}
respond();
