<?php

namespace Askedio\LaravelRachet;

/**
 * Example Rachet Server example, onMessage sends some stuff and closes connection.
 */
class RachetServer extends Contracts\RachetServer
{
    public function onMessage(ConnectionInterface $conn, $input)
    {
        parent::onMessage($conn, $input);

        $this->send('Hello you.'.PHP_EOL);

        $this->sendAll('Hello everyone.'.PHP_EOL);

        $this->send('Wait, I don\'t know you! Bye bye!'.PHP_EOL);

        $this->abort();
    }
}
