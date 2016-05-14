<?php

namespace Askedio\LaravelRatchet;

use Ratchet\ConnectionInterface;

/**
 * Echo Server Example.
 */
class RatchetServerExample extends RatchetServer implements Contracts\RatchetServer
{
    public function onMessage(ConnectionInterface $conn, $input)
    {
        parent::onMessage($conn, $input);

        if (!$this->throttled) {
            $this->send($conn, sprintf('- %s', $input));
        }
    }
}
