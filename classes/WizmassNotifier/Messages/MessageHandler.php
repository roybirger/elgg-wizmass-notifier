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

require_once(dirname(__FILE__) . '/../ClientManager.php');

class MessageHandler {


    protected $clientsManager;

    protected $notificationHandler;

    /**
     * MessageHandler constructor.
     */
    public function __construct($tokens)
    {
        //todo: inject
        $this->clientsManager = new ClientsManager($tokens);
        $this->notificationHandler = new NotificationHandler();
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
                echo 'Error: unknown message';
        }

    }

    private function userRegister($client, $data)
    {
        echo "Connection {$client->resourceId} is subscribing for notifications\n";

        if ($this->clientsManager->AddClient($client,$data)) {

            $this->clientsManager->UpdateUserRequest($data);

            $pendingNotifications = $this->notificationHandler->GetPendingNotifications($data);

            $this->clientsManager->SendUserResponse($data->token->user_guid, $pendingNotifications);

        }
        else {

            echo 'could not validate user' . PHP_EOL;

        }
    }

    private function userFetchNotificationsRequest($client, $data)
    {

        echo "Connection {$client->resourceId} is fetching notifications\n";

        if ($this->clientsManager->IsSubscribed($data->token->user_guid))
        {
            $this->clientsManager->UpdateUserRequest($data);
        }
        else {
            echo 'user is not subscribed' . PHP_EOL;
        }
    }

    private function markAsRead($client, $data)
    {
        echo "Connection {$client->resourceId} is marking notification {$data->notificationGuids} as read\n";

        $this->notificationHandler->MarkAsRead($data);
    }

    public function SendMessage($entry)
    {
        $data = json_decode($entry);

        //todo: make effective
        foreach($data->recipient_guids as $user) {

            echo "Sending a notification to user GUID {$user->guid}\n";

            if ($this->clientsManager->IsSubscribed($user->guid))
            {
                $data->data = get_object_vars($data->data);
                $this->clientsManager->SendUserResponse($user->guid, array($data->data));
            }
            else {
                echo 'not subscribed...' . PHP_EOL;
                var_dump($user);
            }
//
//            if (isset($this->subscribers[$user->guid])) {
//                $connection = $this->subscribers[$user->guid];
//
//                $response = array(
//                    'data' => $data->text,
//                    'callbackId' => $connection->callbackId
//                );
//
//                $connection->send(json_encode($response));
//            }
        }
    }
}