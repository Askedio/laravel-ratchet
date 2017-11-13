# Laravel Ratchet Server

**This is an updated fork of [Laravel Ratchet](https://github.com/Askedio/laravel-ratchet), built specifically to work with this [custom fork](https://github.com/simonhamp/echo) of Laravel Echo.**

This fork enables you to create and run a fully functioning WebSocket server in your Laravel app that works with Laravel's built-in [broadcasting](https://laravel.com/docs/5.5/broadcasting).

## Requirements

- PHP 7.1
- ZeroMQ
- ext-zmq for PHP

## Installation

Because this is a custom fork and it relies on another custom package (and because I want to maintain compatibility with the original repos), the installation is a little more complicated (for now). You must do all of this in your `composer.json` manually:

```json
"require": {
    "askedio/laravel-ratchet": "^2.0"
},
"repositories": [
    {
        "type": "git",
        "url":  "git@github.com:simonhamp/laravel-ratchet.git"
    },
    {
        "type": "git",
        "url":  "git@github.com:simonhamp/laravel-zmq.git"
    }
]
```

The service providers are loaded automatically in Laravel 5.5 using Package Autodiscovery.

You **MUST** publish the vendor files so you can configure your server defaults.

```bash
$ php artisan vendor:publish --provider=LaravelRatchetServiceProvider
$ php artisan vendor:publish --provider=ZmqServiceProvider
```

## Starting the Server

The quickest way to start a standard WebSocket server is simply by running:

```bash
$ php artisan ratchet:serve --driver=WsServer
```

This will run a simple example server based on `src/Examples/Pusher.php`.

It's possible to create a WampServer or an IoServer also. Use the `--help` switch on the command to find out more.

You should create your own server class inside your `app` folder by extending one of the core Ratchet server classes: [RatchetWsServer.php](https://github.com/simonhamp/laravel-ratchet/blob/master/src/RatchetWsServer.php) or [RatchetWampServer.php](https://github.com/simonhamp/laravel-ratchet/blob/master/src/RatchetWampServer.php).

Then update your `config/ratchet.php` file to point to your server `class`.

## Use with Laravel Echo and Broadcasting

To use broadcasting in your Laravel app with the server you create, you will need to tell the server to connect to a ZeroMQ socket.

You can do this simply by passing the `-z` option, i.e.:

```bash
$ php artisan ratchet:serve --driver=WsServer -z
```

This will connect to the socket you define in your `config/ratchet.php` settings. **You MUST set the `ratchet.zmq.method` option to `\ZMQ::SOCKET_PULL` to work with broadcasting.**

Set `BROADCASTING_DRIVER=zmq` in your `.env` and add the following ZeroMQ connection settings to your `config/broadcasting.php`:

```php
'connections' => [
    'zmq' => [
        'driver' => 'zmq',
    ],
]
```

And update the `config/zmq.php` with the same socket details, except **set `zmq.connections.publish.method` to `\ZMQ::SOCKET_PUSH`**.

This will use ZeroMQ as the back-channel to broadcast your events from your Laravel application to the Ratchet WebSocket server.

For your web clients to subscribe to channels through Ratchet, you will need to install [this custom fork of Laravel Echo](https://github.com/simonhamp/echo).

## Acknowledgements

This package would not be possible without the initial [awesome work](https://github.com/Askedio/laravel-ratchet) of [@gcphost](https://github.com/gcphost) of [Asked.io](https://medium.com/asked-io).

Also, thanks to [@pelim](https://github.com/pelim) for creating his original [ZeroMQ broadcasting driver](https://github.com/pelim/laravel-zmq) for Laravel.
