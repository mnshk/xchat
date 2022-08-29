<?php

require_once "../../res/includes/main.php";

$profileUsername = $db->real_escape_string($req['profileUsername']);
$profileEmail = $db->real_escape_string($req['profileEmail']);
$profilePassword = password_hash($db->real_escape_string($req['profilePassword']), PASSWORD_DEFAULT);
$profileName = $db->real_escape_string($req['profileName']);
$profileDescription = "Another XCHAT User";
$profileIconPath = null;
$profileIconVisibility = "public";
$profileDescriptionVisibility = "public";
$profileAccessType = "private";
$profileStatus = "active";
$profileEndpoint = null;


if (isset($_FILES['profileIcon'])) {
    $file = $_FILES['profileIcon'];
    $profileIconPath = uniqid(date('YmdHis_')) . '_' . $profileUsername . '_' . $file['name'];
    if (!move_uploaded_file($file['tmp_name'], $profileIconUploadPath . $profileIconPath)) {
        $res['message'] = "ERROR: Failed to upload profile icon.";
        respond();
    }
} else {
    $profileIconPath = null;
}

$result = $db->query("Insert into profiles (profileUsername, profileEmail, profilePassword, profileName, profileDescription, profileIconPath, profileIconVisibility, profileDescriptionVisibility, profileAccessType, profileStatus, profileEndpoint) values ('$profileUsername', '$profileEmail', '$profilePassword', '$profileName', '$profileDescription', '$profileIconPath', '$profileIconVisibility', '$profileDescriptionVisibility', '$profileAccessType', '$profileStatus', '$profileEndpoint')");

if ($result) {
    $res["status"] = true;
} else {
    $res["message"] = "ERROR: Query failed.";
}
respond();
