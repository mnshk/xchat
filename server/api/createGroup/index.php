<?php

require_once "../../res/includes/main.php";

$groupUsername = $db->real_escape_string($req['groupUsername']);
$groupOwnerProfileID = $db->real_escape_string($req['groupOwnerProfileID']);
$groupName = $db->real_escape_string($req['groupName']);
$groupDescription = "Another XCHAT Group";
$groupIconPath = null;
$groupIconVisibility = "public";
$groupDescriptionVisibility = "public";
$groupAccessType = "private";
$groupStatus = "active";
$time = date('Y-m-d H:i:s');

$res["status"] = true;

if (isset($_FILES['groupIcon'])) {
    $file = $_FILES['groupIcon'];
    $groupIconPath = uniqid(date('YmdHis_')) . '_' . $groupUsername . '_' . $file['name'];
    if (!move_uploaded_file($file['tmp_name'], $groupIconUploadPath . $groupIconPath)) {
        error("Failed to upload group icon");
    }
}

if (!$db->query("INSERT INTO groups (groupUsername, groupOwnerProfileID, groupName, groupDescription, groupIconPath, groupIconVisibility, groupDescriptionVisibility, groupAccessType, groupStatus) values ('$groupUsername', '$groupOwnerProfileID', '$groupName', '$groupDescription', '$groupIconPath', '$groupIconVisibility', '$groupDescriptionVisibility', '$groupAccessType', '$groupStatus')")) {
    error("Failed to add group", $db->error);
}

$groupID = $db->insert_id;
$res["data"]["groupID"] = $groupID;

if (!$db->query("INSERT INTO connections (activeProfileID, otherProfileID, connectionType, connectionStatus) VALUES ('$groupOwnerProfileID', '$groupID', 'group', 'connected')")) {
    error("Failed to add owner connection", $db->error);
}
if (!$db->query("INSERT INTO connections (activeProfileID, otherProfileID, connectionType, connectionStatus) VALUES ('$groupID', '$groupOwnerProfileID', 'group', 'connected')")) {
    error("Failed to add owner connection", $db->error);
}
respond();
