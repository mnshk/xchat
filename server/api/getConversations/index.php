<?php

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

require_once "../../res/includes/main.php";
require_once "../../res/includes/sendNotification.php";

$activeProfileID = $db->real_escape_string($req["activeProfileID"]);
$conversationType = $db->real_escape_string($req["conversationType"]);
$dateTime = date("Y-m-d H:i:s");

$res["status"] = true;
$res['data']['conversations'] = array();

if ($conversationType == "profile") {
    $profilesToBeNotified = $db->query("SELECT `activeProfileID` FROM `conversations` WHERE (`otherProfileID` = '$activeProfileID' AND `conversationSentStatus` = 'Sent' AND `conversationType` = 'profile')");

    if (!$profilesToBeNotified) {
        error("Failed to get profiles to be notified", $db->error);
    }

    $tempArray = array();
    while ($row = $profilesToBeNotified->fetch_assoc()) {
        $tempArray[] = $row['activeProfileID'];
    }

    $profilesToBeNotified = $tempArray;

    if (!$db->query("UPDATE `conversations` SET `conversationSentStatus` = 'Delivered', `conversationLastUpdatedOn` = '$dateTime' WHERE  (`otherProfileID` = '$activeProfileID' AND `conversationSentStatus` = 'Sent')")) {
        error("Failed to update conversation sent status", $db->error);
    }
    if (!$db->query("UPDATE `messages` SET `messageStatus` = 'Delivered', `messageReceivedOn` = '$dateTime' WHERE (`messageRecipientProfileID` = '$activeProfileID' AND `messageStatus` = 'Sent')")) {
        error("Failed to update message status", $db->error);
    }
} else if ($conversationType == "group") {

    $messageIDs = $db->query("SELECT `messageID` from `messagesdeliverystatus` WHERE `messageStatus` = 'Sent' AND `profileID` = '$activeProfileID'");
    if (!$messageIDs) {
        error("Failed to get message IDs", $db->error);
    }

    $groupIDs = [];

    while ($messageID = $messageIDs->fetch_assoc()) {
        $messageID = $messageID["messageID"];
        if (!$groupID = $db->query("SELECT `messageRecipientProfileID` FROM `messages` WHERE `messageID` = '$messageID'")) {
            error("Failed to get message", $db->error);
        }
        $groupIDs[] = $groupID->fetch_assoc()["messageRecipientProfileID"];
    }

    $groupIDs = array_unique($groupIDs);

    $groupMembers = [];

    foreach ($groupIDs as $groupID) {
        $members = $db->query("SELECT `otherProfileID` FROM `connections` WHERE (`activeProfileID` = '$groupID' AND `otherProfileID` != '$activeProfileID' AND `connectionStatus` = 'connected')");
        if (!$members) {
            error("Failed to get members", $db->error);
        }
        while ($member = $members->fetch_assoc()) {
            $groupMembers[] = (int)$member["otherProfileID"];
        }
    }

    $profilesToBeNotified = $groupMembers;
    // $profilesToBeNotified = null;

    if (!$db->query("UPDATE `messagesdeliverystatus` SET `messageStatus` = 'Delivered', `messageReceivedOn` = '$dateTime' WHERE (`profileID` = '$activeProfileID' AND `messageStatus` = 'Sent')")) {
        error("Failed to update message status", $db->error);
    }
}

$conversations = $db->query("SELECT * FROM `conversations` WHERE (`activeProfileID` = '$activeProfileID' AND `conversationType` = '$conversationType') ORDER BY `conversationLastUpdatedOn` DESC");

if (!$conversations) {
    error("Failed to get conversations", $db->error);
}

if ($conversations->num_rows > 0) {
    while ($row = $conversations->fetch_assoc()) {
        $res['data']['conversations'][] = $row;
    }
}

if ($profilesToBeNotified) {
    $notificationType = $conversationType . "ConversationUpdate";
    if (!sendBatchNotifications($profilesToBeNotified, array(
        "type" => $notificationType,
    ))) {
        error("Failed to send notifications", array(
            "type" => $notificationType,
            "profiles" => $profilesToBeNotified,
        ));
    }
}
respond();
