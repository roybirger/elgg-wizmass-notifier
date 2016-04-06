<?php
/**
 * Created by PhpStorm.
 * User: birger.roy@gmail.com
 * Date: 12/15/2015
 * Time: 2:42 PM
 */

namespace WizmassNotifier\Notifications;

use WizmassNotifier\Interfaces\WizmassNotification;
use WizmassNotifier\Notifications\NotificationFactory;
use Elgg;

class NotificationHandler {

    public function GetPendingNotifications($data)
    {


        $notifications = elgg_get_entities_from_relationship(array(
            'relationship' => \WizmassNotifier\Interfaces\WizmassNotification::HAS_ACTOR,
            'relationship_guid' => $data->token->user_guid,
            'inverse_relationship' => true
        ));

        $readNotifications = elgg_get_entities_from_relationship(array(
            'relationship' => \WizmassNotifier\Interfaces\WizmassNotification::WAS_READ,
            'relationship_guid' => $data->token->user_guid,
            'inverse_relationship' => true
        ));


        $readNotificationsGuids = array_map(function ($item) { return $item->guid; }, $readNotifications);

        echo "Got: " .  count($notifications) . ' unread notifications' . PHP_EOL;
        echo "Got: " .  count($readNotifications) . ' read notifications' . PHP_EOL;

        $notificationFactory = NotificationFactory::getInstance();

        /** @var WizmassNotification[] $notificationsToSend */
        $notificationsToSend = [];

        /** @var \ElggEntity $notification */
        foreach ($notifications as $notification) {

            if (!in_array($notification->guid,$readNotificationsGuids)) {

                /** @var WizmassNotification $wizmassNotification */
                $wizmassNotification = $notificationFactory->Build($notification);

                if (!$wizmassNotification) {
                    echo 'error building notifications' . PHP_EOL;
                }
                else {

                    $notificationsToSend[] = $wizmassNotification->BuildNotificationData();
                }
            }
        }

        return $notificationsToSend;
    }

    public function MarkAsRead($data)
    {
        /** @var /ElggUser $user */
        $user = get_user($data->token->user_guid);

        echo 'count guids:  ' . count($data->notificationGuids);

        if ($user) {

            foreach($data->notificationGuids as $guid) {

                echo 'reading guid: ' . $guid;

                $notification = get_entity($guid);

                if (isset($notification)) {

                    /** @var NotificationFactory $notificationFactory */
                    $notificationFactory = NotificationFactory::getInstance();

                    /** @var WizmassNotification $wizmassNotification */
                    $wizmassNotification = $notificationFactory->Build($notification);

                    $wizmassNotification->markRead($user);

                } else {
                    //todo
                    echo 'error...';
                }
            }
        }


    }
}