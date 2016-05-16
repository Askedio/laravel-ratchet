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
composer require askedio/laravel-ratchet
~~~

Register the `provider` in `config/app.php`.
~~~
Askedio\LaravelRatchet\Providers\LaravelRatchetServiceProvider::class,
~~~

# Example
[RatchetServerExample.php](https://github.com/Askedio/laravel-ratchet/blob/master/src/RatchetServerExample.php), a basic echo server, is used when you do not define a class. Here is another example:
~~~
<?php

namespace App;

use Ratchet\ConnectionInterface;
use Askedio\LaravelRatchet\RatchetServer;

class RatchetServer extends RatchetServer
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
You'll need to change the class to in your command line or config.
~~~
php artisan ratchet:serve --class="App\RatchetServer:"
~~~

# Command Line
To use the default values from the configuration simple run the command as follows:
~~~
php artisan ratchet:serve
~~~
You can also define configuration items on the command line:
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
There are several configuration values that you will want to change. Publish the configuration then you can edit `config/ratchet.php`.
~~~
php artisan vendor:publish --class="\Askedio\LaravelRatchet\Providers\LaravelRatchetServiceProvider::class"
~~~
### Configuration Options
* **class**: Your MessageComponentInterface or WampServerInterface class.
* **host**: The host to listen on.
* **port**: The port to listen on.
* **connectionLimit**: The total number of connections allowed (RatchetServer only).
* **throttle**: [Throttle](https://github.com/GrahamCampbell/Laravel-Throttle) connections and messages.
  * **onOpen**: limit:delay for connections.
  * **onMessage**: limit:delay for messages.
* **abortOnMessageThrottle**: disconnect client when message throttle triggered.
* **blackList**: Collection or Model of the hosts to ban using [IpBlackList](http://socketo.me/docs/black).

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

# Supervisor Configuration
> Supervisor is a client/server system that allows its users to control a number of processes on UNIX-like operating systems.

Things crash and long running processes need to be monitored. We can use [Supervisor](http://supervisord.org/index.html) to help with this.


### Install supervisor.
~~~
sudo apt-get install supervisor
~~~
### Create the config.

Replace `/home/forge/app.com/` with the path to your application.
~~~
sudo cat <<EOF > /etc/supervisor/conf.d/laravel-ratchet.conf
[program:laravel-ratchet]
process_name=%(program_name)s_%(process_num)02d
command=php /home/forge/app.com/artisan ratchet:serve -q
autostart=true
autorestart=true
user=vagrant
numprocs=1
redirect_stderr=true
stdout_logfile=/home/forge/app.com/ratchet.log
EOF
~~~
### Enable & Start.
~~~
sudo supervisorctl reread

sudo supervisorctl update

supervisorctl start laravel-ratchet:*
~~~


# Testing
See contributing.

# Contributing
Write some tests, that'd be swell.
