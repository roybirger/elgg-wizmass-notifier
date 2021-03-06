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
use Monolog\Logger;

class NotificationHandler {

    protected $logger;

    /**
     * NotificationHandler constructor.
     * @param Logger $logger
     */
    public function __construct($logger) {
        $this->logger = $logger;
    }

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

        $this->logger->info('processing ' . count($notifications) . ' notifications');

        $notificationFactory = NotificationFactory::getInstance();

        /** @var WizmassNotification[] $notificationsToSend */
        $notificationsToSend = [];

        /** @var \ElggEntity $notification */
        foreach ($notifications as $notification) {

            /** @var WizmassNotification $wizmassNotification */
            $wizmassNotification = $notificationFactory->Build($notification);

            if (!$wizmassNotification) {
                $this->logger->info('error building notification: ' . $notification->guid);
            }
            else {

                $data = $wizmassNotification->BuildNotificationData();

                if (!in_array($notification->guid,$readNotificationsGuids)) {
                    $data['read'] = false;
                }
                else {
                    $data['read'] = true;
                }

                $notificationsToSend[] = $data;
            }
        }

        return $notificationsToSend;
    }

    public function MarkAsRead($data)
    {
        /** @var /ElggUser $user */
        $user = get_user($data->token->user_guid);

        if ($user) {

            foreach($data->notificationGuids as $guid) {

                $notification = get_entity($guid);

                if (isset($notification)) {

                    /** @var NotificationFactory $notificationFactory */
                    $notificationFactory = NotificationFactory::getInstance();

                    /** @var WizmassNotification $wizmassNotification */
                    $wizmassNotification = $notificationFactory->Build($notification);

                    $wizmassNotification->markRead($user);

                } else {
                    //todo
                    $this->logger->info('error while marking as read. notification: ' . $guid);
                }
            }
        }


    }
}