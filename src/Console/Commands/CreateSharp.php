<?php

namespace Roster\Console\Commands;

use Roster\Filesystem\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateSharp extends Command
{
    /**
     * Configure
     *
     */
    protected function configure()
    {
        $this->setName("create:sharp")
            ->setDescription("Create sharp.");
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
        $this->createSharp($input, $output);
    }

    /**
     * Create sharp
     *
     * @param $input
     * @param $output
     * @return mixed
     */
    protected function createSharp($input, $output)
    {
        $stub = File::where('src.Console.Commands.stubs', 'create_sharp', 'stub')
            ->getContent();

        if (!File::where('app.Sharp', 'Statements')->exist())
        {
            if (!File::isDir('app.Sharp'))
            {
                File::makeDir('app.Sharp');
            }

            File::create($stub, 'app.Sharp', 'Statements');

            return $output->writeln('<fg=green>Sharp created.</>');
        }

        return $output->writeln('<fg=green>Sharp already exist!</>');

        }

}
