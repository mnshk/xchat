<?php

require_once "../../res/includes/main.php";
require_once "../../res/includes/sendNotification.php";

$activeProfileID = $db->real_escape_string($req["activeProfileID"]);
$otherProfileID = $db->real_escape_string($req["otherProfileID"]);
$conversationType = $db->real_escape_string($req["messageConversationType"]);
$dateTime = date("Y-m-d H:i:s");
$res['status'] = true;

$res['data']['messages'] = array();

if ($conversationType == "profile") {

    $profilesToBeNotified = $db->query("SELECT `activeProfileID` FROM `conversations` WHERE (`otherProfileID` = '$activeProfileID' AND `conversationSentStatus` = 'Delivered' AND `conversationType` = 'profile')");

    if (!$profilesToBeNotified) {
        error("Failed to get profiles to be notified", $db->error);
    }

    $tempArray = array();
    while ($row = $profilesToBeNotified->fetch_assoc()) {
        $tempArray[] = $row['activeProfileID'];
    }

    $profilesToBeNotified = $tempArray;

    if (!$db->query("UPDATE `conversations` SET `conversationSentStatus` = 'Seen', `conversationLastUpdatedOn` = '$dateTime' WHERE  (`otherProfileID` = '$activeProfileID' AND `conversationSentStatus` = 'Delivered')")) {
        error("Failed to update conversation sent status", $db->error);
    }
    if (!$db->query("UPDATE `messages` SET `messageStatus` = 'Seen', `messageSeenOn` = '$dateTime' WHERE (`messageRecipientProfileID` = '$activeProfileID' AND `messageStatus` = 'Delivered')")) {
        error("Failed to update message status", $db->error);
    }
    $messages = $db->query("SELECT * FROM `messages` WHERE (((`messageSenderProfileID` = '$activeProfileID' AND `messageRecipientProfileID` = '$otherProfileID') OR (`messageSenderProfileID` = '$otherProfileID' AND `messageRecipientProfileID` = '$activeProfileID')) AND `messageConversationType` = 'profile') ORDER BY messageSentOn ASC");
} else if ($conversationType == "group") {

    $messageIDs = $db->query("SELECT `messageID` from `messagesdeliverystatus` WHERE `messageStatus` = 'Delivered' AND `profileID` = '$activeProfileID'");
    if (!$messageIDs) {
        error("Failed to get message IDs", $db->error);
    }

    $groupIDs = [];

    while ($messageID = $messageIDs->fetch_assoc()) {
        $messageID = $messageID["messageID"];
        if (!$groupID = $db->query("SELECT `messageRecipientProfileID` FROM `messages` WHERE `messageID` = '$messageID'")) {
            error("Failed to get message", $db->error);
        }
        if ($groupID->num_rows > 0) {
            $groupIDs[] = $groupID->fetch_assoc()["messageRecipientProfileID"];
        }
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

    if (!$db->query("UPDATE `messagesdeliverystatus` SET `messageStatus` = 'Seen', `messageSeenOn` = '$dateTime' WHERE (`profileID` = '$activeProfileID' AND `messageStatus` = 'Delivered')")) {
        error("Failed to update message status", $db->error);
    }
    $messages = $db->query("SELECT * FROM messages WHERE (((messageSenderProfileID = '$otherProfileID') OR (messageRecipientProfileID = '$otherProfileID')) AND `messageConversationType` = 'group') ORDER BY messageSentOn ASC");
}

if (!$db->query("UPDATE conversations SET conversationNewReceived = 0 WHERE (activeProfileID = '$activeProfileID' AND otherProfileID = '$otherProfileID' AND conversationType = '$conversationType')")) {
    error("Failed to update conversation", $db->error);
}

if (!$messages) {
    error("Failed to get messages", $db->error);
}


if ($messages->num_rows > 0) {
    while ($row = $messages->fetch_assoc()) {
        if ($row["messageType"] == "image" || $row["messageType"] == "video" || $row["messageType"] == "document") {
            $row["messageFilePath"] = $mediaURL . $row["messageFilePath"];
        }
        $res['data']['messages'][] = $row;
    }
}

if ($profilesToBeNotified) {
    $notificationType = $conversationType . "MessagesUpdate";
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
