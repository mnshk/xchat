<?php

require_once "../../res/includes/main.php";


$keyword = $req['keyword'];
$searchType = $req['searchType'];

if ($searchType == "profile") {
    $result = $db->query("SELECT * FROM profiles WHERE (profileAccessType != 'hidden' AND (profileName LIKE '%$keyword%' OR profileUsername LIKE '%$keyword%')) ORDER BY profileName ASC");
} else if ($searchType == "group") {
    $result = $db->query("SELECT * FROM groups WHERE (groupAccessType != 'hidden' AND (groupName LIKE '%$keyword%' OR groupUsername LIKE '%$keyword%')) ORDER BY groupName ASC");
}

if ($result) {
    $res['data'] = array();
    if ($searchType == "profile") {
        while ($row = $result->fetch_assoc()) {
            if ($row['profileID'] != $req['activeProfileID']) {
                $isConnected = false;
                $con = $db->query("SELECT * FROM connections WHERE (activeProfileID = '$req[activeProfileID]' AND otherProfileID = '$row[profileID]' AND `connectionType` = '$searchType') ");
                if ($con->num_rows > 0 && $con->fetch_assoc()['connectionStatus'] == 'connected') {
                    $isConnected = true;
                }
                if ($row['profileIconVisibility'] == "private") {
                    if (!$isConnected) {
                        $row['profileIconPath'] = "";
                    }
                }
                if ($row['profileDescriptionVisibility'] == "private") {
                    if (!$isConnected) {
                        $row['profileDescription'] = "<i class='small text-secondary'>Connect to read description</i>";
                    }
                }
                if ($row["profileIconPath"]) {
                    $row["profileIconPath"] = $profileIconURL . $row["profileIconPath"];
                }
                $res['data'][] = $row;
            }
        }
    } elseif ($searchType == "group") {
        while ($row = $result->fetch_assoc()) {
            $isConnected = false;
            $con = $db->query("SELECT * FROM connections WHERE (activeProfileID = '$req[activeProfileID]' AND otherProfileID = '$row[groupID]' AND `connectionType` = '$searchType') ");
            if ($con->num_rows > 0 && $con->fetch_assoc()['connectionStatus'] == 'connected') {
                $isConnected = true;
            }
            if ($row['groupIconVisibility'] == "private") {
                if (!$isConnected) {
                    $row['groupIconPath'] = "";
                }
            }
            if ($row['groupDescriptionVisibility'] == "private") {
                if (!$isConnected) {
                    $row['groupDescription'] = "<i class='small text-secondary'>Connect to read description</i>";
                }
            }
            if ($row["groupIconPath"]) {
                $row["groupIconPath"] = $groupIconURL . $row["groupIconPath"];
            }
            $res['data'][] = $row;
        }
    }
    $res['status'] = true;
} else {
    $res['message'] = "ERROR: Query failed.";
}

respond();
