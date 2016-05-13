<?php

namespace Askedio\LaravelRatchet;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

/**
 * Quick cp of Ratchets Pusher example.
 */
class PusherServer implements WampServerInterface
{

    /**
     * An RPC call has been received
     * @param \Ratchet\ConnectionInterface $conn
     * @param string                       $id The unique ID of the RPC, required to respond to
     * @param string|Topic                 $topic The topic to execute the call against
     * @param array                        $params Call parameters received from the client
     */
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        //
    }

    /**
    * A request to subscribe to a topic has been made
    * @param \Ratchet\ConnectionInterface $conn
    * @param string|Topic                 $topic The topic to subscribe to
    */
    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        //
    }

    /**
    * A request to unsubscribe from a topic has been made
    * @param \Ratchet\ConnectionInterface $conn
    * @param string|Topic                 $topic The topic to unsubscribe from
    */
    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        //
    }

    /**
    * A client is attempting to publish content to a subscribed connections on a URI
    * @param \Ratchet\ConnectionInterface $conn
    * @param string|Topic                 $topic The topic the user has attempted to publish to
    * @param string                       $event Payload of the publish
    * @param array                        $exclude A list of session IDs the message should be excluded from (blacklist)
    * @param array                        $eligible A list of session Ids the message should be send to (whitelist)
    */
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        //
    }

    /**
     * When a new connection is opened it will be passed to this method
     * @param  ConnectionInterface $conn The socket/connection that just connected to your application
     * @throws \Exception
     */
    public function onOpen(ConnectionInterface $conn)
    {
        //
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     * @param  ConnectionInterface $conn The socket/connection that is closing/closed
     * @throws \Exception
     */
    public function onClose(ConnectionInterface $conn)
    {
        //
    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     * @param  ConnectionInterface $conn
     * @param  \Exception          $e
     * @throws \Exception
     */
    public function onError(ConnectionInterface $conn, \Exception $exception)
    {
        //
    }
}
