<?php

namespace Askedio\LaravelRatchet;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

abstract class RatchetServer implements MessageComponentInterface
{
    /**
     * Clients.
     *
     * @var [type]
     */
    protected $clients;

    /**
     * Console.
     *
     * @var [type]
     */
    protected $console;

    /**
     * Set clients and console.
     *
     * @param [type] $console [description]
     */
    public function __construct($console)
    {
        $this->clients = new \SplObjectStorage();
        $this->console = $console;
    }

    /**
     * Perform action on open.
     *
     * @param ConnectionInterface $conn [description]
     *
     * @return [type] [description]
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        $this->console->info(sprintf('New connection! (%s)', $conn->resourceId));

        $connections = count($this->clients);
        $this->console->info(sprintf('%d connection%s\n', $connections, $connections == 1 ? '' : 's'));

        if ($connectionLimit = config('ratchet.connectionLimit')) {
            if ($connections - 1 >= $connectionLimit) {
                $this->console->info(sprintf('To many connections %d of %d\n', $connections - 1, $connectionLimit));
                $conn->send('to_many_connections');
                $conn->close();
            }
        }
    }

    /**
     * Perform action on message.
     *
     * @param ConnectionInterface $conn  [description]
     * @param [type]              $input [description]
     *
     * @return [type] [description]
     */
    public function onMessage(ConnectionInterface $conn, $input)
    {
        $this->console->info(sprintf('Connection %d sent message "%s"'."\n", $conn->resourceId, $input));
    }

    /**
     * Perform action on close.
     *
     * @param ConnectionInterface $conn [description]
     *
     * @return [type] [description]
     */
    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        $this->console->info(sprintf('Connection (%s) has disconnected', $conn->resourceId));
    }

    /**
     * Perform action on error.
     *
     * @param ConnectionInterface $conn      [description]
     * @param Exception           $exception [description]
     *
     * @return [type] [description]
     */
    public function onError(ConnectionInterface $conn, \Exception $exception)
    {
        $conn->close();
        $this->console->error(sprintf('An error has occurred: %s', $exception->getMessage()));
    }

    /**
     * Close the current connection.
     *
     * @return [type] [description]
     */
    public function abort(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        $conn->close();
    }

    /**
     * Send a message to the current connection.
     *
     * @param [type] $message [description]
     *
     * @return [type] [description]
     */
    public function send(ConnectionInterface $conn, $message)
    {
        $conn->send($message);
    }

    /**
     * Send a message to all connections.
     *
     * @param [type] $message [description]
     *
     * @return [type] [description]
     */
    public function sendAll($message)
    {
        foreach ($this->clients as $client) {
            $client->send($message);
        }
    }
}
