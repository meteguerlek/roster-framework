<?php

namespace Roster\Console\Commands;

use Roster\Filesystem\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateMail extends Command
{
    /**
     * Configure
     *
     */
    protected function configure()
    {
        $this->setName("create:mail")
            ->setDescription("Create mail")
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
        $this->createMail($input, $output);

    }

    /**
     * Create model
     *
     * @param $input
     * @param $output
     * @return mixed
     */
    protected function createMail($input, $output)
    {
        $stub = File::where('src.Console.Commands.stubs', 'create_mail', 'stub')
            ->getContent();

        $disk = config('disk.mail');

        $class = explode('/', $input->getArgument('name'));
        $className = array_pop($class);
        $namespace = implode('\\', array_merge(array_map(function($p){
            return ucfirst($p);
        }, explode('.', $disk)), $class));

        $stub = str_replace('{{className}}', $className, $stub);

        $stub = str_replace('{{namespace}}', $namespace, $stub);

        File::create($stub, $disk.'.'.implode('.', $class), $className);

        return $output->writeln('<fg=green>Mail '.$input->getArgument('name').' created.</>');

    }
}
