<?php

namespace Roster\Support;

use App\Commands\Kernel;
use Symfony\Component\Console\Input\ArrayInput;

class Roster
{
    protected static $console = null;

    public static function call(string $command, array $options = [])
    {
        $arrayInput = array_merge(['command' => $command], $options);

        if (static::$console)
        {
            return static::$console->run(new ArrayInput($arrayInput));
        }

        $consoleKernel = new Kernel();
        $consoleKernel->addCommands();

        return $consoleKernel->run(new ArrayInput($arrayInput));
    }
}
