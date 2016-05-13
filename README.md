![Header](http://i.imgur.com/H1OQeOV.png)

# Laravel Ratchet Server
An Artisan Command for running your own Ratchet Server with Laravel 5.2.

# What's Inside
The Artisan Command will start a io or ws ratchet server with the `MessageComponentInterface` class of your making. I've added a few functions like `abort` `send` and `sendAll` to make some common tasks easier.


# Installation
Install with composer
~~~
composer require askedio/laravel-ratchet:dev-master
~~~
Register in the providers array in `config/app.php`
~~~
Askedio\LaravelRatchet\Providers\LaravelRatchetServiceProvider::class,
~~~

# Configuration
You can configure the default host, port, class and max connections in `config/ratchet.php`, publish the config to make adjustments.
~~~
php artisan vendor:publish --class=Askedio\LaravelRatchet\Providers\LaravelRatchetServiceProvider::class
~~~

# Example
`RatchetServerExample.php` is the default class used for the Ratchet Server, it's really simple. Here is a copy you could use.
~~~
<?php

namespace App;

use Ratchet\ConnectionInterface;

class RatchetServer extends \Askedio\LaravelRatchet\RatchetServer
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
~~~
You'll need to change the class to `App\RatchetServer::class` in your command line or config.
~~~
php artisan ratchet:serv --class=App\RatchetServer::class
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


# Testing
There are no tests and I don't care to write them at this time.

# Contribute
Write some tests, that'd be swell.
