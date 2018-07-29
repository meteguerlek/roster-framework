<?php

namespace Roster\Console\Commands;

use Roster\Filesystem\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateMiddleware extends Command
{
    /**
     * Configure
     *
     */
    protected function configure()
    {
        $this->setName("create:middleware")
            ->setDescription("Create middleware with default content.")
            ->addArgument('name', InputArgument::REQUIRED)
            ->setHelp('How do you call it?');
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
        $this->createController($input, $output);
    }

    /**
     * Create Middleware
     *
     * @param $input
     * @param $output
     * @return mixed
     */
    protected function createController($input, $output)
    {
        $stub = File::where('src.Console.Commands.stubs', 'create_middleware', 'stub')
            ->getContent();

        $stub = str_replace('ClassName', $input->getArgument('name'), $stub);

        File::create($stub, config('disk.middleware'), $input->getArgument('name'));

        return $output->writeln('<fg=green>Middleware '.$input->getArgument('name').' created.</>');
    }
}
