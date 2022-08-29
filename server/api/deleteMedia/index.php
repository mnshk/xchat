<?php

require_once "../../res/includes/main.php";

$filePath = $db->real_escape_string($req['filePath']);
$res["status"] = true;

$filePath = str_replace($groupIconURL, $groupIconUploadPath, $filePath);
$filePath = str_replace($profileIconURL, $profileIconUploadPath, $filePath);

if (!unlink($filePath)) {
    error("Failed to delete file");
}
respond();
