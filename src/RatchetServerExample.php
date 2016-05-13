<?php

namespace Askedio\LaravelRatchet;

use Ratchet\ConnectionInterface;

/**
 * Echo Server Example.
 */
class RatchetServerExample extends RatchetServer
{
    public function onMessage(ConnectionInterface $conn, $input)
    {
        parent::onMessage($conn, $input);

        $this->send($conn, sprintf('- %s', $input));
    }
}
