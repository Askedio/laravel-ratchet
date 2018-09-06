<?php

namespace Askedio\LaravelRatchet\Console\Commands;

use Ratchet\Http\HttpServer;
use Ratchet\Wamp\WampServer;
use Ratchet\Server\IoServer;
use Ratchet\Server\IpBlackList;
use Ratchet\WebSocket\WsServer;
use Illuminate\Console\Command;
use React\EventLoop\LoopInterface;
use React\ZMQ\Context as ZMQContext;
use Ratchet\Wamp\WampServerInterface;
use Ratchet\MessageComponentInterface;
use React\Socket\Server as SocketServer;
use React\EventLoop\Factory as EventLoop;
use Askedio\LaravelRatchet\RatchetWsServer;
use Askedio\LaravelRatchet\RatchetWampServer;
use Symfony\Component\Console\Input\InputOption;

class RatchetServerCommand extends Command
{
    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'ratchet:serve';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start Ratchet Server';

    /**
     * Server host.
     *
     * @var string
     */
    protected $host;

    /**
     * Server port.
     *
     * @var int
     */
    protected $port;

    /**
     * The class to use for the server.
     *
     * @var string
     */
    protected $class;

    /**
     * The type of server to boot.
     *
     * @var string
     */
    protected $driver;

    /**
     * Keep alive interval.
     *
     * @var int
     */
    protected $keepAlive;

    /**
     * SSL/TLS Options.
     *
     * @var array
     */
    protected $tls;

    /**
     * The ReactPHP event loop.
     *
     * @var LoopInterface
     */
    protected $eventLoop;

    /**
     * The mutable server instance.
     *
     * @var mixed
     */
    protected $serverInstance;

    /**
     * The original instance of $this->class
     */
    protected $ratchetServer;

    /**
     * WebSocket server instance.
     */
    protected $wsServerInstance;

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['host', null, InputOption::VALUE_OPTIONAL, 'Ratchet server host', config('ratchet.host', '0.0.0.0')],
            ['port', 'p', InputOption::VALUE_OPTIONAL, 'Ratchet server port', config('ratchet.port', 8080)],
            ['class', null, InputOption::VALUE_OPTIONAL, 'Class that implements MessageComponentInterface.', config('ratchet.class')],
            ['driver', null, InputOption::VALUE_OPTIONAL, 'Ratchet connection driver [IoServer|WsServer|WampServer]', 'WampServer'],
            ['zmq', 'z', null, 'Bind server to a ZeroMQ socket (always on for WampServer)'],
            ['keepAlive', null, InputOption::VALUE_OPTIONAL, 'Option to enable WebSocket server keep alive [interval in seconds]', config('ratchet.keepAlive', 0)],
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->host = $this->option('host');

        $this->port = intval($this->option('port'));

        $this->class = $this->option('class');

        $this->driver = $this->option('driver');

        $this->keepAlive = $this->option('keepAlive');

        $this->tls = config('ratchet.tls', []);
        $peer_name = env('APP_URL');
        $pos = strpos($peer_name, '://');
        if ($pos !== false) {
            $peer_name = substr($peer_name, $pos+3);
        }
        $this->tls += [
            'peer_name' => $peer_name
        ];

        $this->startServer();
    }

    /**
     * Start the appropriate server.
     *
     * @param string $driver
     */
    private function startServer($driver = null)
    {
        if (! $driver) {
            $driver = $this->driver;
        }

        $this->info(sprintf('Starting %s server on: %s:%d', $this->option('driver'), $this->host, $this->port));

        $this->createServerInstance();

        $this->{'start'.$driver}()->run();
    }

    /**
     * Get/generate the server instance from the class provided.
     */
    private function createServerInstance()
    {
        if (! $this->serverInstance instanceof $this->class) {
            $class = $this->class;
            $this->serverInstance = $this->ratchetServer = new $class($this);
        }
    }

    /**
     * Decorate a server instance with a blacklist instance and block any blacklisted addresses.
     */
    private function bootWithBlacklist()
    {
        $this->serverInstance = new IpBlackList($this->serverInstance);

        foreach (config('ratchet.blackList') as $host) {
            $this->serverInstance->blockAddress($host);
        }
    }

    /**
     * Decorate the server instance with a WebSocket server.
     *
     * @param bool $withZmq
     * @return IoServer
     */
    private function bootWebSocketServer($withZmq = false)
    {
        if ($withZmq || $this->option('zmq')) {
            $this->bootZmqConnection();
        }

        $this->wsServerInstance = new WsServer($this->serverInstance);

        $this->serverInstance = new HttpServer(
            $this->wsServerInstance
        );

        if($this->keepAlive > 0){
            $this->wsServerInstance->enableKeepAlive($this->getEventLoop(), $this->keepAlive);
        }

        return $this->bootIoServer();
    }

    /**
     * Deploy a WampServer
     *
     * @return IoServer
     */
    private function startWampServer()
    {
        if (! $this->serverInstance instanceof RatchetWampServer) {
            throw new \Exception("{$this->class} must be an instance of ".RatchetWampServer::class." to create a Wamp server");
        }

        // Decorate the server instance with a WampServer
        $this->serverInstance = new WampServer($this->serverInstance);

        return $this->bootWebSocketServer(true);
    }

    /**
     * Deploy a WsServer.
     *
     * @return IoServer
     */
    private function startWsServer()
    {
        if (! $this->serverInstance instanceof RatchetWsServer) {
            throw new \Exception("{$this->class} must be an instance of ".RatchetWsServer::class." to create a WebSocket server");
        }

        $this->bootWithBlacklist();

        return $this->bootWebSocketServer();
    }

    /**
     * Deploy an IoServer only
     *
     * @return IoServer
     */
    private function startIoServer()
    {
        $this->bootWithBlacklist();

        return $this->bootIoServer();
    }

    /**
     * Create the IoServer instance to encapsulate our server in.
     *
     * @return IoServer
     */
    private function bootIoServer()
    {
        $context = [
            'tls' => $this->tls,
        ];
        $socket = new SocketServer($this->host.':'.$this->port, $this->getEventLoop(), $context);

        return new IoServer(
            $this->serverInstance,
            $socket,
            $this->getEventLoop()
        );
    }

    /**
     * Boot a ZMQ listener and let the Ratchet server handle its events.
     */
    private function bootZmqConnection()
    {
        $this->info(sprintf('Starting ZMQ listener on: %s:%s', config('ratchet.zmq.host'), config('ratchet.zmq.port')));

        $context = new ZMQContext($this->getEventLoop());
        $socket = $context->getSocket(config('ratchet.zmq.method', \ZMQ::SOCKET_PULL));
        $socket->bind(sprintf('tcp://%s:%d', config('ratchet.zmq.host', '127.0.0.1'), config('ratchet.zmq.port', 5555)));

        $socket->on('messages', function ($messages) {
            $this->ratchetServer->onEntry($messages);
        });

        $socket->on('message', function ($message) {
            $this->ratchetServer->onEntry($message);
        });
    }

    /**
     * Generate and return a React EventLoop object.
     *
     * @return LoopInterface
     */
    private function getEventLoop()
    {
        if (! $this->eventLoop instanceof LoopInterface) {
            $this->eventLoop = EventLoop::create();
        }

        return $this->eventLoop;
    }
}
