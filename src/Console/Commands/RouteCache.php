<?php

namespace Roster\Console\Commands;

use Roster\Filesystem\File;
use Roster\Routing\Router as Route;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RouteCache extends Command
{
    /**
     * Configure
     *
     */
    protected function configure()
    {
        $this->setName("route:cache")
            ->setDescription("Cache routes.");
    }

    /**
     * Handler
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        require_once File::where('routes', 'web')->getPath();

        $routes =  base64_encode(serialize(['routes' => Route::getRoutes(), 'names' => Route::getNames()]));

        $stub = File::where('src.Console.Commands.stubs', 'route_cache', 'stub')->getContent();

        $routes = str_replace('routes', $routes, $stub);

        File::create($routes, config('disk.storage.cache'), 'routes');

        return $output->writeln('<fg=green>Routes are cached.</>');
    }
}
