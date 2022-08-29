<?php

require_once "../../res/includes/main.php";

$activeProfileID = $db->real_escape_string($req["activeProfileID"]);

$result = $db->query("SELECT * FROM groups WHERE groupOwnerProfileID = '$activeProfileID'");

if ($result) {
    $res["status"] = true;
    $res["data"] = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row["groupIconPath"] = $row["groupIconPath"] == "" ? "" : $groupIconURL . $row["groupIconPath"];
            $res["data"][] = $row;
        }
    }
} else {
    $res["message"] = "ERROR: Query failed.";
}

respond();
