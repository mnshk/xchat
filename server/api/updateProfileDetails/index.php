<?php

require_once "../../res/includes/main.php";

$profileID = $db->real_escape_string($req['profileID']);
$res["status"] = true;

$query = "UPDATE profiles SET ";

if (isset($req["profileName"])) {
    $profileName = $db->real_escape_string($req['profileName']);
    $query .= "profileName = '$profileName', ";
}
if (isset($req["profileDescription"])) {
    $profileDescription = $db->real_escape_string($req['profileDescription']);
    $query .= "profileDescription = '$profileDescription', ";
}
if (isset($req["profileIconVisibility"])) {
    $profileIconVisibility = $db->real_escape_string($req['profileIconVisibility']);
    $query .= "profileIconVisibility = '$profileIconVisibility', ";
}
if (isset($req["profileDescriptionVisibility"])) {
    $profileDescriptionVisibility = $db->real_escape_string($req['profileDescriptionVisibility']);
    $query .= "profileDescriptionVisibility = '$profileDescriptionVisibility', ";
}
if (isset($req["profileAccessType"])) {
    $profileAccessType = $db->real_escape_string($req['profileAccessType']);
    $query .= "profileAccessType = '$profileAccessType', ";
}

if (isset($_FILES['profileIcon'])) {
    $file = $_FILES['profileIcon'];
    $profileUsername = $db->query("SELECT profileUsername FROM profiles WHERE profileID = '$profileID'")->fetch_assoc()["profileUsername"];
    $profileIconPath = uniqid(date('YmdHis_')) . '_' . $profileUsername . '_' . $file['name'];
    if (move_uploaded_file($file['tmp_name'], $profileIconUploadPath . $profileIconPath)) {
        $res["msdf"] = "ipsdfsd";
        $query .= "profileIconPath = '$profileIconPath', ";
    } else {
        $res['message'] = "ERROR: Failed to upload profile icon.";
        $res["status"] = false;
    }
} else {
    $profileIconPath = null;
}

$query = rtrim($query, ", ");
$query .= "WHERE profileID = '$profileID'";

if (!$db->query($query)) {
    error("ERROR: Failed to update profile details.");
}

respond();
