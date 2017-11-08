<?php

namespace Askedio\LaravelRatchet\Console\Commands;

use Illuminate\Console\Command;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\Server\IpBlackList;
use Ratchet\Wamp\WampServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Input\InputOption;

class RatchetServerCommand extends Command
{
    /**
     * The console command name.
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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->fire();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->host = $this->option('host');

        $this->port = intval($this->option('port'));

        $this->info(sprintf('Starting %s server on: %s:%d', $this->option('driver'), $this->host, $this->port));

        $this->server($this->option('driver'))->run();
    }

    /**
     * Get the IO driver.
     *
     * @param [type] $driver [description]
     *
     * @return [type] [description]
     */
    private function getDriver($driver)
    {
        $class = $this->option('class');

        $ratchetServer = new IpBlackList(new $class($this));

        foreach (config('ratchet.blackList')->all() as $host) {
            $ratchetServer->blockAddress($host);
        }

        if ($driver == 'WsServer') {
            return $this->getWsServerDriver($ratchetServer);
        }

        return $ratchetServer;
    }

    /**
     * Get the WsServer driver.
     *
     * @param [type] $ratchetServer [description]
     *
     * @return [type] [description]
     */
    private function getWsServerDriver($ratchetServer)
    {
        return new HttpServer(
            new WsServer(
                $ratchetServer
            )
        );
    }

    /**
     * Get the WampServer driver.
     *
     * @param [type] $ratchetServer [description]
     *
     * @return [type] [description]
     */
    private function startWampServer()
    {
        $loop = \React\EventLoop\Factory::create();

        $class = $this->option('class');

        $ratchetServer = new $class($this);

        $this->info(sprintf('Starting ZMQ server on: %s:%s', config('ratchet.zmq.host'), config('ratchet.zmq.port')));

        $context = new \React\ZMQ\Context($loop);
        $pull = $context->getSocket(\ZMQ::SOCKET_PULL);
        $pull->bind(sprintf('tcp://%s:%d', config('ratchet.zmq.host'), config('ratchet.zmq.port')));

        $pull->on('message', function ($message) use ($ratchetServer) {
            $ratchetServer->onEntry($message);
        });


        $webSock = new \React\Socket\Server($loop);
        $webSock->listen($this->port, $this->host);
        $webServer = new \Ratchet\Server\IoServer(
            new \Ratchet\Http\HttpServer(
                new \Ratchet\WebSocket\WsServer(
                    new \Ratchet\Wamp\WampServer(
                        $ratchetServer
                    )
                )
            ),
            $webSock
        );

        return $loop;
    }

    /**
     * Return the IoServer factory.
     *
     * @param [type] $driver [description]
     *
     * @return [type] [description]
     */
    private function server($driver)
    {
        if ($driver == 'WampServer') {
            return $this->startWampServer();
        }

        return IoServer::factory(
            $this->getDriver($driver),
            $this->port,
            $this->host
        );
    }

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
        ];
    }
}
