<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json; charset=UTF-8");

date_default_timezone_set("Asia/Kolkata");

error_reporting(0);

include_once '../../res/composer/vendor/autoload.php';

$res = array(
    "status" => false,
    "message" => "",
    "data" => array()
);

$req;

$serverPath = $_SERVER['DOCUMENT_ROOT'] . "/XCHAT/v1.0.0/server/";
// $serverPath = $_SERVER['DOCUMENT_ROOT'] . "/XCHAT/server/";
$serverURL = "https://munish11.000webhostapp.com/XCHAT/v1.0.0/server/";
// $serverURL = "https://localhost/XCHAT/server/";
$profileIconUploadPath = $serverPath . "data/users/profileIcons/";
$groupIconUploadPath = $serverPath . "data/users/groupIcons/";
$mediaUploadPath = $serverPath . "data/media/";
$profileIconURL = $serverURL . "data/users/profileIcons/";
$groupIconURL = $serverURL . "data/users/groupIcons/";
$mediaURL = $serverURL . "data/media/";

function respond()
{
    global $res;
    echo json_encode($res);
}

function error($message, $data = array())
{
    echo json_encode(array(
        "status" => false,
        "error" => $message,
        "data" => $data
    ));
    exit();
}

// local dev
// $db = new mysqli("localhost", "root", "", "xchat");
$db = new mysqli("localhost", "id18681443_root", "F8tE^mS*]*/3q{&N", "id18681443_xchat");

if ($db->connect_errno) {
    error("Database connection failed", $db->connect_error);
} else {
    $db->set_charset("utf8");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $req = $_POST;
} else if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $req = $_GET;
} else {
    error("Invalid request method", $_SERVER["REQUEST_METHOD"]);
}
