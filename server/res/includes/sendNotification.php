<?php

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

function sendNotification($profileID, $payload)
{
    global $db;
    $profileEndpoint = json_decode($db->query("SELECT profileEndpoint FROM profiles WHERE profileID = '$profileID'")->fetch_assoc()['profileEndpoint'], true);
    $VAPIDKeys = $db->query("SELECT * FROM vapidkeys")->fetch_assoc();

    if ($profileEndpoint != "" || $profileEndpoint != null) {
        $auth = [
            'VAPID' => [
                'subject' => 'mailto:send2munish@gmail.com',
                'publicKey' => $VAPIDKeys['publicKey'],
                'privateKey' => $VAPIDKeys['privateKey']
            ],
        ];
        $webPush = new WebPush($auth);
        $subscription = Subscription::create([
            'endpoint' => $profileEndpoint["endpoint"],
            'keys' => [
                'auth' => $profileEndpoint["keys"]["auth"],
                'p256dh' => $profileEndpoint['keys']['p256dh']
            ]
        ]);
        if ($webPush->sendOneNotification($subscription, json_encode($payload))->isSuccess()) {
            return true;
        } else {
            return false;
        }
    }
    return true;
}

function sendBatchNotifications($profileIDs, $payload)
{
    global $db;

    $VAPIDKeys = $db->query("SELECT * FROM vapidkeys")->fetch_assoc();
    $auth = [
        'VAPID' => [
            'subject' => 'mailto:send2munish@gmail.com',
            'publicKey' => $VAPIDKeys['publicKey'],
            'privateKey' => $VAPIDKeys['privateKey']
        ],
    ];
    $webPush = new WebPush($auth);

    foreach ($profileIDs as $profileID) {
        $profileEndpoint = json_decode($db->query("SELECT profileEndpoint FROM profiles WHERE profileID = '$profileID'")->fetch_assoc()['profileEndpoint'], true);

        if ($profileEndpoint != "" || $profileEndpoint != null) {
            $subscription = Subscription::create([
                'endpoint' => $profileEndpoint["endpoint"],
                'keys' => [
                    'auth' => $profileEndpoint["keys"]["auth"],
                    'p256dh' => $profileEndpoint['keys']['p256dh']
                ]
            ]);
            $webPush->queueNotification($subscription, json_encode($payload));
        }
    }
    foreach ($webPush->flush() as $report) {
        if (!$report->isSuccess()) {
            // echo $report->getReason();
            return false;
        }
    }
    return true;
}
