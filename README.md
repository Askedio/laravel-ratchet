![Header](http://i.imgur.com/OmPzal8.png)

# Laravel Rachet Server
An Artisan Command for running your own Rachet Server with Laravel 5.2.

# What's Inside
The Artisan Command will start a io or ws rachet server with the `MessageComponentInterface` class of your making. I've added a few functions like `abort` `send` and `sendAll` to make some common tasks easier.


# Installation
Install with composer
~~~
composer require askedio/laravel-rachet:dev-master
~~~
Register in the providers array in `config/app.php`
~~~
Askedio\LaravelRachet\Providers\LaravelRachetServiceProvider::class,
~~~

# Configuration
You can configure the default host, port, class and max connections in `config/rachet.php`, publish the config to make adjustments.
~~~
php artisan vendor:publish --class=Askedio\LaravelRachet\Providers\LaravelRachetServiceProvider::class
~~~

# Example
`RachetServerExample.php` is the default class used for the Rachet Server, it's really simple. Here is a version you could use.
~~~
<?php

namespace App;

use Ratchet\ConnectionInterface;

class RachetServer extends \Askedio\LaravelRache\RachetServer
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
You'll need to change the class to `App\RachetServerExample::class` in your command line or config.
~~~
php artisan rachet:serv --class=App\RachetServerExample::class
~~~

# Serve
~~~
▶ php artisan rachet:serve  --help
Usage:
  rachet:serve [options]

Options:
      --host[=HOST]      Rachet server host [default: "0.0.0.0"]
  -p, --port[=PORT]      Rachet server port [default: "9090"]
      --class[=CLASS]    Class that implements MessageComponentInterface. [default: "Askedio\LaravelRachet\RachetServerExample"]
      --driver[=DRIVER]  Rachet connection driver [IoServer|WsServer] [default: "IoServer"]
~~~


# Testing
There are no tests and I don't care to write them at this time.

# Contribute
Write some tests, that'd be swell.
