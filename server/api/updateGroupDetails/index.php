<?php

require_once "../../res/includes/main.php";

$groupID = $db->real_escape_string($req['groupID']);
$res["status"] = true;

$query = "UPDATE groups SET ";

if (isset($req["groupName"])) {
    $groupName = $db->real_escape_string($req['groupName']);
    $query .= "groupName = '$groupName', ";
}
if (isset($req["groupDescription"])) {
    $groupDescription = $db->real_escape_string($req['groupDescription']);
    $query .= "groupDescription = '$groupDescription', ";
}
if (isset($req["groupIconVisibility"])) {
    $groupIconVisibility = $db->real_escape_string($req['groupIconVisibility']);
    $query .= "groupIconVisibility = '$groupIconVisibility', ";
}
if (isset($req["groupDescriptionVisibility"])) {
    $groupDescriptionVisibility = $db->real_escape_string($req['groupDescriptionVisibility']);
    $query .= "groupDescriptionVisibility = '$groupDescriptionVisibility', ";
}
if (isset($req["groupAccessType"])) {
    $groupAccessType = $db->real_escape_string($req['groupAccessType']);
    $query .= "groupAccessType = '$groupAccessType', ";
}

if (isset($_FILES['groupIcon'])) {
    $file = $_FILES['groupIcon'];
    $groupUsername = $db->query("SELECT groupUsername FROM groups WHERE groupID = '$groupID'")->fetch_assoc()["groupUsername"];
    $groupIconPath = uniqid(date('YmdHis_')) . '_' . $groupUsername . '_' . $file['name'];
    if (!move_uploaded_file($file['tmp_name'], $groupIconUploadPath . $groupIconPath)) {
        $res['message'] = "ERROR: Failed to upload group icon.";
        $res["status"] = false;
    } else {
        $query .= "groupIconPath = '$groupIconPath', ";
    }
} else {
    $profileIconPath = null;
}

$query = rtrim($query, ", ");
$query .= "WHERE groupID = '$groupID'";

if (!$db->query($query)) {
    error("Failed to update group details.");
}

respond();
