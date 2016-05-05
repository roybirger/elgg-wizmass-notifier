<?php
/**
 * Created by PhpStorm.
 * User: birger.roy@gmail.com
 * Date: 12/1/2015
 * Time: 7:21 PM
 */

namespace WizmassNotifier;

//use Elgg\Logger;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\WsServerInterface;
use Ratchet\MessageComponentInterface;
use WizmassNotifier\Messages\MessageHandler;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;


class Pusher implements MessageComponentInterface {


    const LOG_FILE_NAME = '/tmp/push-server.log';
    protected $clients;

    protected $messageHandler;

    protected $resourceToUserData;

    protected $logger;

    public function __construct($tokens) {
        $this->clients = new \SplObjectStorage;
        $this->resourceToUserData = Array();
        $this->logger = new Logger('push_server_logger');
        $this->logger->pushHandler(new StreamHandler(self::LOG_FILE_NAME,Logger::DEBUG));
        $this->logger->pushHandler(new FirePHPHandler());
        $this->messageHandler = new MessageHandler($tokens,$this->logger);
        $this->logger->info('push server started');


    }

    public function getSubProtocols() {
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        $this->logger->info("New Connection from a client. Resource ID:  {$conn->resourceId}");
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
        $this->logger->info("Connection {$conn->resourceId} has disconnected");
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
        $this->logger->error("An error has occurred: {$e->getMessage()}");
        $conn->close();
    }
}
