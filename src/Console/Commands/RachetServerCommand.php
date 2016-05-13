<?php

namespace Askedio\LaravelRachet\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Askedio\LaravelRachet\RachetServer;

class RachetServerCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rachet:serve';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start Rachet Server';

    /**
     * Server host.
     * @var string
     */
    protected $host;

    /**
     * Server port.
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
     * @param  [type] $driver [description]
     * @return [type]         [description]
     */
    private function getDriver($driver)
    {
        $class = $this->option('class');

        $rachetServer = new $class($this);

        if ($driver == 'WsServer') {
            return $this->getWsServerDriver($rachetServer);
        }

        return $rachetServer;
    }

    /**
     * Get the WsServer driver.
     * @param  [type] $rachetServer [description]
     * @return [type]               [description]
     */
    private function getWsServerDriver($rachetServer)
    {
        return new HttpServer(
            new WsServer(
                $rachetServer
            )
        );
    }

    /**
     * Return the IoServer factory.
     * @param  [type] $driver [description]
     * @return [type]         [description]
     */
    private function server($driver)
    {
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
            ['host', null, InputOption::VALUE_OPTIONAL, 'Rachet server host', config('rachet.host', '0.0.0.0')],
            ['port', 'p', InputOption::VALUE_OPTIONAL, 'Rachet server port', config('rachet.port', 9090)],
            ['class', null, InputOption::VALUE_OPTIONAL, 'Class that implements MessageComponentInterface.', config('rachet.class')],
            ['driver', null, InputOption::VALUE_OPTIONAL, 'Rachet connection driver [IoServer|WsServer]', 'IoServer'],
        ];
    }
}
