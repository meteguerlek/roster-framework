<?php
namespace App\Commands;

use Roster\Console\ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define your commands
     *
     * @var array
     */
    protected $commands = [
        CreateCompany::class,
        CreateEmployees::class,
        DatabaseTruncate::class
    ];
}
