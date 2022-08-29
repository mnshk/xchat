<?php

require_once "../../res/includes/main.php";

$activeProfileID = $db->real_escape_string($req["activeProfileID"]);
$otherProfileID = $db->real_escape_string($req["otherProfileID"]);
$messageType = $db->real_escape_string($req["messageType"]);
$messageConversationType = $db->real_escape_string($req["messageConversationType"]);
$messageData = $req["messageData"];
$messageKey = $req["messageKey"];
$messageFilePath = "";
$dateTime = date("Y-m-d H:i:s");

$res['status'] = true;

if ($messageType == "video" || $messageType == "image" || $messageType == "document") {
    $file = $_FILES['messageFile'];
    $messageData = $file['name'];
    $messageFilePath = uniqid(date('YmdHis_')) . '_' . $activeProfileID . '_to_' . $otherProfileID . '_' . $file['name'];
    if (!move_uploaded_file($file['tmp_name'], $mediaUploadPath . $messageFilePath)) {
        error("Failed to upload media", $file['tmp_name']);
    }
}

// check if conversation exists! if not, create it!

if ($messageConversationType == "profile") {
    $conversation = $db->query("SELECT * FROM conversations WHERE (activeProfileID = '$activeProfileID' AND otherProfileID = '$otherProfileID' AND conversationType = '$messageConversationType')");
    if (!$conversation) {
        error("Failed to fetch conversation", $db->error);
    }
    if ($conversation->num_rows == 0) {
        if (!$db->query("INSERT INTO conversations (activeProfileID, otherProfileID, conversationStatus, conversationType) VALUES ('$activeProfileID', '$otherProfileID', 'active', '$messageConversationType')")) {
            error("Failed to create conversation", $db->error);
        }
        if (!$db->query("INSERT INTO conversations (activeProfileID, otherProfileID, conversationStatus, conversationType) VALUES ('$otherProfileID', '$activeProfileID', 'active', '$messageConversationType')")) {
            error("Failed to create conversation", $db->error);
        }
    }
} else if ($messageConversationType == "group") {
    $groupMembers = $db->query("SELECT `activeProfileId` FROM `connections` WHERE (otherProfileID = '$otherProfileID' AND connectionStatus = 'connected' AND connectionType = 'group')");
    if (!$groupMembers) {
        error("Failed to fetch group members", $db->error);
    }
    if ($groupMembers->num_rows > 0) {
        while ($member = $groupMembers->fetch_assoc()) {
            $memberProfileID = $member["activeProfileId"];
            $conversation = $db->query("SELECT * FROM conversations WHERE (activeProfileID = '$memberProfileID' AND otherProfileID = '$otherProfileID' AND conversationType = '$messageConversationType')");
            if (!$conversation) {
                error("Failed to fetch conversation", $db->error);
            }
            if ($conversation->num_rows == 0) {
                if (!$db->query("INSERT INTO conversations (activeProfileID, otherProfileID, conversationStatus, conversationType) VALUES ('$memberProfileID', '$otherProfileID', 'active', '$messageConversationType')")) {
                    error("Failed to create conversation", $db->error);
                }
            }
        };
    }
}


// Insert the message into the database
if (!$db->query("INSERT INTO messages (messageSenderProfileID, messageRecipientProfileID, messageType, messageData, messageFilePath, messageKey, messageSentOn, messageStatus, messageConversationType) VALUES ('$activeProfileID', '$otherProfileID', '$messageType', '$messageData', '$messageFilePath', '$messageKey', '$dateTime', 'Sent', '$messageConversationType')")) {
    error("Failed to insert message", $db->error);
}
$messageID = $db->insert_id;
// Update sender's last message sent
if (!$db->query("UPDATE conversations SET conversationSentStatus = 'Sent', conversationLastUpdatedOn = '$dateTime' WHERE (activeProfileID = '$activeProfileID' AND otherProfileID = '$otherProfileID' AND conversationType = '$messageConversationType')")) {
    error("Failed to update conversation", $db->error);
}

// Update recipient's last message received
if ($messageConversationType == "profile") {
    if (!$db->query("UPDATE conversations SET conversationNewReceived = conversationNewReceived + 1, conversationLastUpdatedOn = '$dateTime' WHERE (activeProfileID = '$otherProfileID' AND otherProfileID = '$activeProfileID' AND conversationType = '$messageConversationType')")) {
        error("Failed to update conversation", $db->error);
    }
} else if ($messageConversationType == "group") {
    $profiles = $db->query("SELECT * FROM connections WHERE (otherProfileID = '$otherProfileID' AND activeProfileID != '$activeProfileID' AND connectionType = 'group')");
    if (!$profiles) {
        error("Failed to fetch group members", $db->error);
    }
    while ($profile = $profiles->fetch_assoc()) {
        $profileID = $profile["activeProfileID"];
        if (!$db->query("UPDATE conversations SET conversationNewReceived = conversationNewReceived + 1, conversationLastUpdatedOn = '$dateTime' WHERE (activeProfileID = '$profileID' AND otherProfileID = '$otherProfileID' AND conversationType = 'group')")) {
            error("Failed to update conversation", $db->error);
        }
        if (!$db->query("INSERT INTO messagesdeliverystatus (messageID, profileID, messageSentOn, messageStatus) VALUES ('$messageID', '$profileID', '$dateTime', 'Sent')")) {
            error("Failed to insert message delivery status", $db->error);
        }
    }
}

respond();

require_once "../../res/includes/sendNotification.php";

if ($messageConversationType == "profile") {
    $senderProfile = $db->query("SELECT profileName, profileIconPath FROM profiles WHERE profileID = '$activeProfileID'")->fetch_assoc();
    if (!$senderProfile) {
        error("Failed to fetch sender profile", $db->error);
    }
    if (!sendNotification($otherProfileID, array(
        "type" => "newMessage",
        "from" => $senderProfile["profileName"],
        "icon" => $profileIconURL . $senderProfile["profileIconPath"],
    ))) {
        error("Failed to send notification", $db->error);
    }
} else if ($messageConversationType == "group") {
    $senderGroup = $db->query("SELECT groupName, groupIconPath FROM groups WHERE groupID = '$otherProfileID'")->fetch_assoc();
    if (!$senderGroup) {
        error("Failed to fetch sender group", $db->error);
    }
    $groupMembers = $db->query("SELECT activeProfileID FROM connections WHERE (otherProfileID = '$otherProfileID' AND activeProfileID !='$activeProfileID' AND connectionStatus = 'connected' AND connectionType = 'group')");
    if (!$groupMembers) {
        error("Failed to fetch group members", $db->error);
    }
    if ($groupMembers->num_rows > 0) {
        $tempArray = array();
        while ($groupMember = $groupMembers->fetch_assoc()) {
            $tempArray[] = $groupMember['activeProfileID'];
        }
        if (!sendBatchNotifications($tempArray, array(
            "type" => "newGroupMessage",
            "from" => $senderGroup["groupName"],
            "icon" => $groupIconURL . $senderGroup["groupIconPath"],
        ))) {
            error("Failed to send notification", $db->error);
        }
    }
}
