<?php

namespace Roster\Console\Commands;

use Roster\Filesystem\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateRule extends Command
{
    /**
     * Configure
     *
     */
    protected function configure()
    {
        $this->setName("create:rule")
            ->addArgument('name', InputArgument::REQUIRED, 'Name?')
            ->setDescription("Create rule.");
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
        $this->createRule($input, $output);
    }

    /**
     * Create validation
     *
     * @param $input
     * @param $output
     * @return mixed
     */
    protected function createRule($input, $output)
    {
        $stub = File::where('src.Console.Commands.stubs', 'create_rule', 'stub')
            ->getContent();

        $disk = config('disk.rules');

        $class = explode('/', $input->getArgument('name'));
        $className = array_pop($class);
        $namespace = implode('\\', array_merge(array_map(function($p){
            return ucfirst($p);
        }, explode('.', $disk)), $class));

        $stub = str_replace('{{className}}', $className, $stub);

        $stub = str_replace('{{namespace}}', $namespace, $stub);

        File::create($stub, $disk.'.'.implode('.', $class), $className);

        return $output->writeln('<fg=green>Model '.$input->getArgument('name').' created.</>');
    }

}
