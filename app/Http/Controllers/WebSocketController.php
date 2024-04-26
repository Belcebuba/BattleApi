<?php
// app/Http/Controllers/WebSocketController.php

namespace App\Http\Controllers;

use Ratchet\ConnectionInterface;
use BeyondCode\LaravelWebSockets\WebSockets\WebSocketHandler;

class WebSocketController implements \Ratchet\WebSocket\MessageComponentInterface
{
    protected $handler;

    public function __construct(WebSocketHandler $handler)
    {
        $this->handler = $handler;
    }

    public function onOpen(ConnectionInterface $connection)
    {
        $this->handler->onOpen($connection);
    }

    public function onClose(ConnectionInterface $connection)
    {
        $this->handler->onClose($connection);
    }

    public function onError(ConnectionInterface $connection, \Exception $exception)
    {
        $this->handler->onError($connection, $exception);
    }

    public function onMessage(ConnectionInterface $connection, $payload)
    {
        $this->handler->onMessage($connection, $payload);
    }
}
