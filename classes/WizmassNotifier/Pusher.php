<?php
/**
 * Created by PhpStorm.
 * User: birger.roy@gmail.com
 * Date: 12/1/2015
 * Time: 7:21 PM
 */

namespace WizmassNotifier;

use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\WsServerInterface;
use Ratchet\MessageComponentInterface;
use WizmassNotifier\Messages\MessageHandler;

class Pusher implements MessageComponentInterface {



    protected $clients;

    protected $messageHandler;

    public function __construct($tokens) {
        $this->clients = new \SplObjectStorage;
        $this->messageHandler = new MessageHandler($tokens);
    }

    public function getSubProtocols() {
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        echo "New connection from a client! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {

        $this->messageHandler->HandleMessage($from, $msg);
    }

    public function onSubscribe(ConnectionInterface $conn, $topic) {
        error_log("Subscribing to a topic");
        $this->subscribedTopics[$topic->getId()] = $topic;
    }

    /**
     * @param string JSON'ified string we'll receive from ZeroMQ
     */
    public function onNotificationMessage($entry) {

        $this->messageHandler->HandleMessage(null,json_encode(array(
            'type' => 'send',
            'data' => $entry
        )));
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }

    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->close();
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}
