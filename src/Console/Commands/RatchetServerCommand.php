<?php

namespace Askedio\LaravelRatchet\Console\Commands;

use Illuminate\Console\Command;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\Server\IpBlackList;
use Ratchet\Wamp\WampServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Input\InputOption;
use React\EventLoop\Factory as LoopFactory;


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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->host = $this->option('host');

        $this->port = intval($this->option('port'));

        $this->info(sprintf('Starting server on: %s:%s', $this->host, $this->port));

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
        //$class = $this->option('class');

        //$ratchetServer = new IpBlackList(new $class($this));

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
    private function getWampServerDriver($ratchetServer)
    {
        return $this->getWsServerDriver(
            new WampServer(
                $ratchetServer
            )
        );
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
            $class = $this->option('class');
            $pusher = new \Askedio\LaravelRatchet\PusherServer();

            $loop   = \React\EventLoop\Factory::create();
            $cb = array($pusher, 'timedCallback');
            $loop->addPeriodicTimer(10, function() use ($cb){
              return $cb;
            });
            $client = new \Predis\Async\Client('tcp://127.0.0.1:6379', $loop);
            $client->connect(array($pusher, 'init'));

            $webSock = new \React\Socket\Server($loop);
            $webSock->listen(8080, '0.0.0.0');
            $webServer = new \Ratchet\Server\IoServer(
                new \Ratchet\WebSocket\WsServer(
                    new \Ratchet\Wamp\WampServer(
                        $pusher
                    )
                ),
                $webSock
            );

            return $loop;
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
            ['port', 'p', InputOption::VALUE_OPTIONAL, 'Ratchet server port', config('ratchet.port', 9090)],
            ['class', null, InputOption::VALUE_OPTIONAL, 'Class that implements MessageComponentInterface.', config('ratchet.class')],
            ['driver', null, InputOption::VALUE_OPTIONAL, 'Ratchet connection driver [IoServer|WsServer|WampServer]', 'WsServer'],
        ];
    }
}
