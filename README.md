![Header](http://i.imgur.com/H1OQeOV.png)

This package provides the artisan command `ratchet:serve` that will start a [Ratchet](http://socketo.me/) [Io Server](http://socketo.me/docs/server),  [Web Socket](http://socketo.me/docs/websocket),  or [Wamp Server](http://socketo.me/docs/wamp) with the class of your making. Included are a few functions like `abort()` `send()` and `sendAll()` to make some common tasks easier.

# Supports
* WaServer, WampServer & IoServer
* IpBlackList
* Connection throttling
* Message throttling




# Installation
Install with composer
~~~
composer require askedio/laravel-ratchet:dev-master
~~~

Register in the providers array in `config/app.php`
~~~
Askedio\LaravelRatchet\Providers\LaravelRatchetServiceProvider::class,
~~~

# Example
[RatchetServerExample.php](https://github.com/Askedio/laravel-ratchet/blob/master/src/RatchetServerExample.php) is the default class used for the Ratchet Server, a basic echo server. Here is another example:
~~~
<?php

namespace App;

use Ratchet\ConnectionInterface;

class RatchetServer extends \Askedio\LaravelRatchet\RatchetServer
{
    public function onMessage(ConnectionInterface $conn, $input)
    {
        parent::onMessage($conn, $input);

        if (!$this->throttled) {
            $this->send($conn, 'Hello you.');

            $this->sendAll('Hello everyone.');

            $this->send($conn, 'Wait, I don\'t know you! Bye bye!');

            $this->abort($conn);
        }
    }
}
~~~
You'll need to change the class to `App\RatchetServer::class` in your command line or config.
~~~
php artisan ratchet:serve --class=App\RatchetServer::class
~~~

# Command Line
~~~
php artisan ratchet:serve  --help

Usage:
  ratchet:serve [options]

Options:
      --host[=HOST]      Ratchet server host [default: "0.0.0.0"]
  -p, --port[=PORT]      Ratchet server port [default: "9090"]
      --class[=CLASS]    Class that implements MessageComponentInterface. [default: "Askedio\LaravelRatchet\RatchetServerExample"]
      --driver[=DRIVER]  Ratchet connection driver [IoServer|WsServer] [default: "IoServer"]
~~~


# Configuration
You can configure the default host, port, class and max connections in `config/ratchet.php`, publish the config to make adjustments.
~~~
php artisan vendor:publish --class=Askedio\LaravelRatchet\Providers\LaravelRatchetServiceProvider::class
~~~
### Configuration Options
* class: Your MessageComponentInterface or WampServerInterface class (or the packages wrappers).
* host: The host to listen on.
* port: The port to listen on.
* connectionLimit: The total number of connections allowed (RatchetServer only).
* throttle: Throttle connections and messages with [Throttle](https://github.com/GrahamCampbell/Laravel-Throttle)
  * onOpen: limit:delay for connections.
  * onMessage: limit:delay for messages.
* abortOnMessageThrottle:
* blackList: Collection or Model of the hosts to ban using [IpBlackList](http://socketo.me/docs/black).

# Options
Send a message to the current connection.
~~~
$this->send($conn, $message);
~~~
Send a message to all connections.
~~~
$this->sendAll($message);
~~~
Close current connection.
~~~
$this->abort($conn);
~~~

# Testing
See contributing.

# Contributing
Write some tests, that'd be swell.
