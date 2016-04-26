<?php
/**
 * Created by PhpStorm.
 * User: birger.roy@gmail.com
 * Date: 12/15/2015
 * Time: 2:08 PM
 */


namespace WizmassNotifier\Messages;

use Ratchet\ConnectionInterface;
use WizmassNotifier\ClientsManager;
use WizmassNotifier\Notifications\NotificationHandler;
use Monolog\Logger;

require_once(dirname(__FILE__) . '/../ClientManager.php');

class MessageHandler {


    protected $clientsManager;

    protected $notificationHandler;

    protected $logger;

    /**
     * MessageHandler constructor.
     * @param $tokens
     * @param Logger $logger
     */
    public function __construct($tokens, $logger)
    {
        //todo: inject
        $this->clientsManager = new ClientsManager($tokens,$logger);
        $this->notificationHandler = new NotificationHandler($logger);
        $this->logger = $logger;
    }

    public function HandleMessage($from, $msg) {

        //todo: validate message
        $data = json_decode($msg);

        switch ($data->type) {
            case 'register':
                $this->userRegister($from, $data);
                break;
            case 'fetch':
                $this->userFetchNotificationsRequest($from, $data);
                break;
            case 'mark_as_read':
                $this->markAsRead($from, $data);
                break;
            case 'send':
                $this->sendMessage($data->data);
                break;
            default:
                $this->logger->error('Error: unknown message type: ' . $data->type);
        }

    }

    private function userRegister($client, $data)
    {
        $this->logger->info("Connection {$client->resourceId} is subscribing for notifications");

        if ($this->clientsManager->AddClient($client,$data)) {

            $this->clientsManager->UpdateUserRequest($data);

            $pendingNotifications = $this->notificationHandler->GetPendingNotifications($data);

            $this->clientsManager->SendUserResponse($data->token->user_guid, $pendingNotifications);

        }
        else {

            $this->logger->info('could not validate user: ' . $data->token->user_guid);

        }
    }

    private function userFetchNotificationsRequest($client, $data)
    {
        $this->logger->info("Connection {$client->resourceId} is fetching notifications");

        if ($this->clientsManager->IsSubscribed($data->token->user_guid))
        {
            $this->clientsManager->UpdateUserRequest($data);
        }
        else {
            $this->logger->info('user is not subscribed. user: ' . $data->token->user_guid);
        }
    }

    private function markAsRead($client, $data)
    {
        $this->logger->info("Connection {$client->resourceId} is marking notification {$data->notificationGuids} as read");

        $this->notificationHandler->MarkAsRead($data);
    }

    public function SendMessage($entry)
    {
        $data = json_decode($entry);

        //todo: make effective
        foreach($data->recipient_guids as $user) {

            $this->logger->info("Sending a notification to user GUID {$user->guid}");

            if ($this->clientsManager->IsSubscribed($user->guid))
            {
                $data->data = get_object_vars($data->data);
                $this->clientsManager->SendUserResponse($user->guid, array($data->data));
            }
            else {
                $this->logger->info("user not subscribed");
            }
        }
    }
}