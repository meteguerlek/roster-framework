<?php

namespace Roster\Console\Commands;

use Roster\Filesystem\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateModel extends Command
{
    /**
     * Configure
     *
     */
    protected function configure()
    {
        $this->setName("create:model")
            ->setDescription("Create model with default content.")
            ->addArgument('name', InputArgument::REQUIRED, 'Who do you want to greet?')
            ->addOption(
                'controller',
                'c',
                InputOption::VALUE_NONE
            )
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
        $this->createModel($input, $output);

    }

    /**
     * Create model
     *
     * @param $input
     * @param $output
     * @return mixed
     */
    protected function createModel($input, $output)
    {
        $stub = File::where('src.Console.Commands.stubs', 'create_model', 'stub')
            ->getContent();

        $disk = config('disk.models');

        $class = explode('/', $input->getArgument('name'));
        $className = array_pop($class);
        $namespace = implode('\\', array_merge(array_map(function($p){
            return ucfirst($p);
        }, explode('.', $disk)), $class));

        $stub = str_replace('{{className}}', $className, $stub);

        $stub = str_replace('{{namespace}}', $namespace, $stub);

        $paths = '';

        foreach ($class as $folder)
        {
            $paths .= '/'.$folder;

            if (!File::isDir($disk.'/'.$paths))
            {
                File::makeDir($disk.'/'.$paths);
            }
        }

        File::create($stub, $disk.'.'.implode('.', $class), $className);

        return $output->writeln('<fg=green>Model '.$input->getArgument('name').' created.</>');

    }
}
