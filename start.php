<?php
/**
 * Wizmass Notifier
 *
 * @package WizmassNotifier
 */

use WizmassNotifier\NotificationFactory;

require_once(dirname(__FILE__) . '/../../engine/start.php');

elgg_register_event_handler('init', 'system', 'wizmass_notifier_init');

/**
 * Initialize the plugin
 *
 * @return void
 */
function wizmass_notifier_init() {

    //elgg_register_notification_event('object', 'wizmass_poll', array('update'));
    //elgg_register_plugin_hook_handler('prepare', 'notification:update:object:wizmass_poll', 'wizmass_notifier_notification_prepare');
    elgg_register_plugin_hook_handler('send', 'notification:update:object:wizmass_poll', 'wizmass_notifier_notification_send');
    //elgg_register_plugin_hook_handler('register', 'register_notification_type', 'wizmass_notifier_register_type');
    //elgg_register_notification_method('wizmass_notifier_notification_send');

}

/**
 * Send real-time notifications to subscribed users
 *
 * @param string $hook   Hook name
 * @param string $type   Hook type
 * @param bool   $result Has anyone sent a message yet?
 * @param array  $params Hook parameters
 * @return bool
 * @access private
 */
function wizmass_notifier_notification_send($hook, $type, $result, $params) {

    /** @var WizmassNotifier\Interfaces\WizmassNotification $notification */
    $notification = $params['notification'];

    echo 'got notification: ' . $notification->guid . PHP_EOL;
    echo 'got subjects: ' . count($notification->getSubjects()) . PHP_EOL;


    require __DIR__ . '/vendor/autoload.php';

    $context = new ZMQContext();
    $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
    $socket->connect("tcp://localhost:5555");

    $subjects = [];
    foreach($notification->getSubjects() as $subject) {
        $subjects[] = array('guid' => $subject->guid);
    }

    $msg = new \stdClass();
    $msg->recipient_guids = $subjects;
    //$msg->recipient_guid = $recipient->guid;
//    $msg->subject_name = $actor->getDisplayName();
//    $msg->subject_url = $actor->getURL();
//    $msg->target_name = $entity->getDisplayName();
//    $msg->target_url = $entity->getURL();
//    $msg->text = $string;
//    $msg->icon_url = $actor->getIconURL();
    $msg->data = $notification->BuildNotificationData();

    echo 'encoded notification data: ' . json_encode($msg) . PHP_EOL;

    $socket->send(json_encode($msg));
}


//function wizmass_notifier_register_type($hook, $type, $result, $params) {
//
//    echo 'Registering type: ' . $params['typeName'] . PHP_EOL;
//
//    /** @var NotificationFactory $notificationFactory */
//    $notificationFactory = NotificationFactory::getInstance();
//    $notificationFactory->RegisterType($params['typeName'],$params['typeBuilder']);
//
//    echo 'hash abx: ' . spl_object_hash($notificationFactory);
//    echo 'types: ' . $notificationFactory->Count();
//}

/**
 * prepare real-time notifications to subscribed users
 *
 * @param string                          $hook         Hook name
 * @param string                          $type         Hook type
 * @param Elgg_Notifications_Notification $notification The notification to prepare
 * @param array                           $params       Hook parameters
 * @return Elgg_Notifications_Notification
 */
function wizmass_notifier_notification_prepare($hook, $type, $notification, $params) {
    $msg = 'prepare notification!!!';
    return $msg;
}