<?php

require_once "../../res/includes/main.php";
require_once "../../res/includes/sendNotification.php";

$activeProfileID = $db->real_escape_string($req["activeProfileID"]);
$otherProfileID = $db->real_escape_string($req["otherProfileID"]);
$connectionType = $db->real_escape_string($req["connectionType"]);
$operation = $req["operation"];

// $res['status'] = true;
// $res['data'] = $action;

if ($connectionType == "profile") {
    $activeProfile = $db->query("SELECT * FROM `profiles` WHERE `profileID` = '$activeProfileID'")->fetch_assoc();
} else if ($connectionType == "group") {
    $activeProfile = $db->query("SELECT * FROM `groups` WHERE `groupID` = '$activeProfileID'")->fetch_assoc();
    if(!isset(($req["operationByGroup"]))){
        $groupOwnerProfileID = $db->query("SELECT `groupOwnerProfileID` FROM `groups` WHERE `groupID` = '$otherProfileID'")->fetch_assoc()['groupOwnerProfileID'];
    }
}


if ($operation == "sendRequest") {

    // $data["act"] = $activeProfile;
    if ($connectionType == "profile") {
        $pat = $db->query("SELECT profileAccessType FROM profiles WHERE profileID = '$otherProfileID'")->fetch_assoc()["profileAccessType"];
    } else if ($connectionType == "group") {
        if (isset($req["operationByGroup"])) {
            $pat = $db->query("SELECT profileAccessType FROM profiles WHERE profileID = '$otherProfileID'")->fetch_assoc()["profileAccessType"];
        } else {
            $pat = $db->query("SELECT groupAccessType FROM groups WHERE groupID = '$otherProfileID'")->fetch_assoc()["groupAccessType"];
        }
    }

    if ($pat == "public") {
        $result = $db->query("INSERT INTO connections (activeProfileID, otherProfileID, connectionStatus, connectionType) VALUES ($activeProfileID, $otherProfileID, 'connected', '$connectionType')");
        $result2 = $db->query("INSERT INTO connections (activeProfileID, otherProfileID, connectionStatus, connectionType) VALUES ($otherProfileID, $activeProfileID, 'connected', '$connectionType')");

        if ($connectionType == "profile") {
            sendNotification($otherProfileID, array(
                "type" => "addedFriend",
                "from" => $activeProfile["profileName"],
                "icon" => $profileIconURL . $activeProfile["profileIconPath"],
            ));
        } else if ($connectionType == "group") {

            $db->query("INSERT INTO conversations (activeProfileID, otherProfileID, conversationStatus, conversationType) VALUES ('$activeProfileID', '$otherProfileID', 'active', 'group')");
            $db->query("INSERT INTO conversations (activeProfileID, otherProfileID, conversationStatus, conversationType) VALUES ('$otherProfileID', '$activeProfileID', 'active', 'group')");

            if (isset($req["operationByGroup"])) {
                sendNotification($otherProfileID, array(
                    "type" => "addedToGroup",
                    "from" => $activeProfile["groupName"],
                    "icon" => $groupIconURL . $activeProfile["groupIconPath"],
                ));
            } else {
                $activeProfile = $db->query("SELECT * FROM `profiles` WHERE `profileID` = '$activeProfileID'")->fetch_assoc();
                sendNotification($groupOwnerProfileID, array(
                    "type" => "joinedGroup",
                    "from" => $activeProfile["profileName"],
                    "icon" => $profileIconURL . $activeProfile["profileIconPath"],
                ));
            }
        }
        $res["message"] = "Added Friend.";
    } elseif ($pat == "private") {
        $result = $db->query("INSERT INTO connections (activeProfileID, otherProfileID, connectionStatus, connectionType) VALUES ($activeProfileID, $otherProfileID, 'sent-request', '$connectionType')");
        $result2 = $db->query("INSERT INTO connections (activeProfileID, otherProfileID, connectionStatus, connectionType) VALUES ($otherProfileID, $activeProfileID, 'received-request', '$connectionType')");

        if ($connectionType == "profile") {
            sendNotification($otherProfileID, array(
                "type" => "friendRequest",
                "from" => $activeProfile["profileName"],
                "icon" => $profileIconURL . $activeProfile["profileIconPath"],
            ));
        } else if ($connectionType == "group") {

            if (isset($req["operationByGroup"])) {
                sendNotification($otherProfileID, array(
                    "type" => "groupInvitation",
                    "from" => $activeProfile["groupName"],
                    "icon" => $groupIconURL . $activeProfile["groupIconPath"],
                ));
            } else {
                $activeProfile = $db->query("SELECT * FROM `profiles` WHERE `profileID` = '$activeProfileID'")->fetch_assoc();
                sendNotification($groupOwnerProfileID, array(
                    "type" => "groupJoinRequest",
                    "from" => $activeProfile["profileName"],
                    "icon" => $profileIconURL . $activeProfile["profileIconPath"],
                ));
            }
        }


        $res["message"] = "Request sent.";
    }
} elseif ($operation == "acceptRequest") {
    $result = $db->query("UPDATE connections SET connectionStatus = 'connected' WHERE (activeProfileID = $activeProfileID AND otherProfileID = $otherProfileID AND connectionType = '$connectionType')");
    $result2 = $db->query("UPDATE connections SET connectionStatus = 'connected' WHERE (activeProfileID = $otherProfileID AND otherProfileID = $activeProfileID AND connectionType = '$connectionType')");

    if ($connectionType == "profile") {
        sendNotification($otherProfileID, array(
            "type" => "friendRequestAccepted",
            "from" => $activeProfile["profileName"],
            "icon" => $profileIconURL . $activeProfile["profileIconPath"],
        ));
    } else if ($connectionType == "group") {

        $db->query("INSERT INTO conversations (activeProfileID, otherProfileID, conversationStatus, conversationType) VALUES ('$activeProfileID', '$otherProfileID', 'active', 'group')");
        $db->query("INSERT INTO conversations (activeProfileID, otherProfileID, conversationStatus, conversationType) VALUES ('$otherProfileID', '$activeProfileID', 'active', 'group')");

        if (isset($req["operationByGroup"])) {
            sendNotification($otherProfileID, array(
                "type" => "groupInvitation",
                "from" => $activeProfile["groupName"],
                "icon" => $groupIconURL . $activeProfile["groupIconPath"],
            ));
        } else {
            $activeProfile = $db->query("SELECT * FROM `profiles` WHERE `profileID` = '$activeProfileID'")->fetch_assoc();
            sendNotification($groupOwnerProfileID, array(
                "type" => "acceptedGroupInvitation",
                "from" => $activeProfile["profileName"],
                "icon" => $profileIconURL . $activeProfile["profileIconPath"],
            ));
        }
    }

    $res["message"] = "Request accepted.";
} elseif ($operation == "deleteConnection") {
    $result = $db->query("DELETE FROM connections WHERE (activeProfileID = $activeProfileID AND otherProfileID = $otherProfileID AND connectionType = '$connectionType')");
    $result2 = $db->query("DELETE FROM connections WHERE (activeProfileID = $otherProfileID AND otherProfileID = $activeProfileID AND connectionType = '$connectionType')");

    $db->query("DELETE FROM conversations WHERE (activeProfileID = $activeProfileID AND otherProfileID = $otherProfileID AND conversationType = '$connectionType')");
    $db->query("DELETE FROM conversations WHERE (activeProfileID = $otherProfileID AND otherProfileID = $activeProfileID AND conversationType = '$connectionType')");

    $res["message"] = "Connection deleted.";
} else {
    $res["message"] = "ERROR: Invalid operation.";
    respond();
}
if ($result && $result2) {
    $res['status'] = true;
} else {
    $res['message'] = "ERROR: Query failed.";
}
respond();
