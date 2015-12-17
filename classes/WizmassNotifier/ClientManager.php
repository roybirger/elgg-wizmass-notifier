<?php
/**
 * Created by PhpStorm.
 * User: birger.roy@gmail.com
 * Date: 12/15/2015
 * Time: 2:21 PM
 */


namespace WizmassNotifier;

use Ratchet\ConnectionInterface;

class ClientsManager {

    /**
     * A lookup of all the topics clients have subscribed to
     */
    protected $subscribers;

    private $tokens;

    /**
     * ClientsManager constructor.
     * @param $tokens
     */
    public function __construct($tokens)
    {
        $this->tokens = $tokens;
        $this->subscribers = array();
    }


    public function AddClient(ConnectionInterface $client, $data)
    {
        if ($this->tokens->validateToken($data->token->token)) {
            // TODO Should the storage be injected?
            // TODO Remove users from the storage when they log out.
            $this->subscribers[$data->token->user_guid] = $client;

            return true;
        }
        else {
            return false;
        }
    }

    public function IsSubscribed($userGuid)
    {
        return isset($this->subscribers[$userGuid]);
    }

    public function UpdateUserRequest($data)
    {
        $this->subscribers[$data->token->user_guid]->callbackId = $data->callbackId;
    }

    public function SendUserResponse($userId, $pendingNotifications)
    {
        $userResponse = [
            'data' => $pendingNotifications,
            'callbackId' => $this->subscribers[$userId]->callbackId
        ];

        echo 'sending: ' . json_encode($userResponse) . PHP_EOL;




        $this->subscribers[$userId]->send(json_encode($userResponse));
    }

}