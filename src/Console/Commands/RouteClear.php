<?php

namespace Roster\Console\Commands;

use Roster\Filesystem\File;
use Roster\Routing\Router as Route;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RouteClear extends Command
{
    /**
     * Configure
     *
     */
    protected function configure()
    {
        $this->setName("route:clear")
            ->setDescription("Clear route cache.");
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
        $file = File::where(config('disk.storage.cache'), 'routes');

        if ($file->exist())
        {
            $file->delete();

            return $output->writeln('<fg=green>Route cache cleared.</>');
        }

        return $output->writeln('<fg=red>Cache not found.</>');
    }
}
