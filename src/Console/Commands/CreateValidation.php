<?php

namespace Roster\Console\Commands;

use Roster\Filesystem\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateValidation extends Command
{
    /**
     * Configure
     *
     */
    protected function configure()
    {
        $this->setName("create:validation")
            ->setDescription("Create validation.");
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
        $this->createValidation($input, $output);
    }

    /**
     * Create validation
     *
     * @param $input
     * @param $output
     * @return mixed
     */
    protected function createValidation($input, $output)
    {
        $stub = File::where('src.Console.Commands.stubs', 'create_validation', 'stub')
            ->getContent();

        if (!File::where('app.Validation', 'Rules')->exist())
        {
            if (!File::isDir('app.Validation'))
            {
                File::makeDir('app.Validation');
            }

            File::create($stub, 'app.Validation', 'Rules');

            return $output->writeln('<fg=green>Validation created.</>');
        }

        return $output->writeln('<fg=green>Validation already exist!</>');

        }

}
