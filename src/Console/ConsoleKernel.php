<?php
namespace Roster\Console;

use Roster\Console\Commands\RouteCache;
use Roster\Console\Commands\RouteClear;
use Roster\Console\Commands\CreateModel;
use Roster\Console\Commands\CreateSharp;
use Roster\Console\Commands\CreateController;
use Roster\Console\Commands\CreateMiddleware;
use Roster\Console\Commands\CreateValidation;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleKernel
{
    /**
     * @var array
     */
    protected $rosterCommands = [
        CreateSharp::class,
        CreateModel::class,
        RouteCache::class,
        RouteClear::class,
        CreateController::class,
        CreateValidation::class,
        CreateMiddleware::class
    ];

    /**
     * @var array
     */
    protected $commands = [];

    /**
     * @var Application
     */
    protected $application = null;

    /**
     * @return array
     */
    public function getCommands()
    {
        return array_merge($this->rosterCommands, $this->commands);
    }

    /**
     * @throws \Exception
     */
    public function addCommands()
    {
        $application = new Application();

        foreach ($this->getCommands() as $command)
        {
            $application->add(new $command);
        }

        $this->application = $application;
    }

    /**
     * @return mixed
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        return $this->application->run($input, $output);
    }
}
