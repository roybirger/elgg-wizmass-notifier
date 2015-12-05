<?php
/**
 * Wizmass Notifier
 *
 * @package WizmassNotifier
 */

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

    echo 'welcome...';

    $notification = $params['notification'];

    echo 'got notification: ' . $notification;

    $event = $params['event'];

    echo 'got event: ' . $event;

//    if (!$event) {
//        // The notification would be incomplete without the event
//        return false;
//    }
//
//    $ia = elgg_set_ignore_access(true);
//
//    $action = $event->getAction();
//    $object = $event->getObject();
//    $string = "river:{$action}:{$object->getType()}:{$object->getSubtype()}";
//    $recipient = $notification->getRecipient();
//    $actor = $event->getActor();
//    switch ($object->getType()) {
//        case 'annotation':
//            // Get the entity that was annotated
//            $entity = $object->getEntity();
//            break;
//        case 'relationship':
//            $entity = get_entity($object->guid_two);
//            break;
//        default:
//            if ($object instanceof ElggComment) {
//                // Use comment's container as notification target
//                $entity = $object->getContainerEntity();
//
//                // Check the action because this isn't necessarily a new comment,
//                // but e.g. someone being mentioned in a comment
//                if ($action == 'create') {
//                    $string = "river:comment:{$entity->getType()}:{$entity->getSubtype()}";
//                }
//            } else {
//                // This covers all other entities
//                $entity = $object;
//            }
//    }
//    elgg_set_ignore_access($ia);

    //return "I've arrived";


    require __DIR__ . '/vendor/autoload.php';

    $context = new ZMQContext();
    $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
    $socket->connect("tcp://localhost:5555");

    $msg = new \stdClass();
    $msg->recipient_guid = 123456789;
    //$msg->recipient_guid = $recipient->guid;
//    $msg->subject_name = $actor->getDisplayName();
//    $msg->subject_url = $actor->getURL();
//    $msg->target_name = $entity->getDisplayName();
//    $msg->target_url = $entity->getURL();
//    $msg->text = $string;
//    $msg->icon_url = $actor->getIconURL();
    $msg->text = 'notification!!!';

    $socket->send(json_encode($msg));
}

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