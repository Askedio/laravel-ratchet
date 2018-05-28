# Laravel Ratchet Server

This package enables you to create and run a fully functioning WebSocket server in your Laravel app. It can optionally receive messages broadcast over ZeroMQ.

## Requirements

- PHP 7.1+
- Laravel 5.5+
- ZeroMQ
- ext-zmq for PHP

## Installation

```bash
$ composer require askedio/laravel-ratchet
```

The service provider is loaded automatically in Laravel 5.5 using Package Autodiscovery.

Publish the vendor files so you can configure your server defaults.

```bash
$ php artisan vendor:publish --provider="Askedio\LaravelRatchet\Providers\LaravelRatchetServiceProvider"
```

## Starting the Server

After completing installation, the quickest way to start a standard WebSocket server is simply by running:

```bash
$ php artisan ratchet:serve --driver=WsServer
```

This will run a simple example server based on `src/Examples/Pusher.php`.

It's possible to create a WampServer or an IoServer also. Use the `--help` switch on the command to find out more.

You should create your own server class inside your `app` folder by extending one of the core Ratchet server classes: [RatchetWsServer.php](https://github.com/Askedio/laravel-ratchet/blob/master/src/RatchetWsServer.php) or [RatchetWampServer.php](https://github.com/Askedio/laravel-ratchet/blob/master/src/RatchetWampServer.php).

Then update your `config/ratchet.php` file to point to your server `class`.

## Use with Laravel Broadcasting

To use broadcasting in your Laravel app with the server you create, you will need a ZeroMQ broadcast driver for Laravel (e.g. [this one](https://github.com/pelim/laravel-zmq)).

You will also need to tell your Ratchet server to bind to a ZeroMQ socket. You can do this simply by passing the `-z` option, i.e.:

```bash
$ php artisan ratchet:serve --driver=WsServer -z
```

This will connect to the socket you define in your `config/ratchet.php` settings and listen for messages from ZeroMQ.

To handle messages published via ZeroMQ, simply add a `public function onEntry($messages)` method to your server class. This will allow you to receive messages inside your Ratchet server instance and determine how to route them.

