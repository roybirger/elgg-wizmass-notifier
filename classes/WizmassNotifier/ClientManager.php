<?php
/**
 * Created by PhpStorm.
 * User: birger.roy@gmail.com
 * Date: 12/15/2015
 * Time: 2:21 PM
 */


namespace WizmassNotifier;

use Ratchet\ConnectionInterface;
use Monolog\Logger;
use Elgg;

class ClientsManager {

    /**
     * A lookup of all the topics clients have subscribed to
     */
    protected $subscribers;

    protected $logger;

    private $tokens;

    /**
     * ClientsManager constructor.
     * @param Tokens $tokens
     * @param Logger $logger
     */
    public function __construct($tokens, $logger)
    {
        $this->tokens = $tokens;
        $this->subscribers = array();
        $this->logger = $logger;
    }


    public function AddClient(ConnectionInterface $client, $data)
    {
        if (!mysql_ping()) {
            $this->logger->info('connection lost. attempting to reconnect');
            $db = _elgg_services()->db;
            $db->setupConnections();
        };

        if (isset($data->token) && $this->tokens->validateToken($data->token->token)) {
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

        $this->logger->info('sending: ' . count($pendingNotifications) . " to user: {$userId}");

        $this->subscribers[$userId]->send(json_encode($userResponse));
    }

}