<?php

define('ROSTER_START', microtime(TRUE));

/**
 * Composer Autoloader
 *
 */

require_once __DIR__ . '/../vendor/autoload.php';
/**
 * Boot app
 *
 */
$app = new Roster\Support\App();

$app->setBasePath(realpath(__DIR__.'/../'));
$app->boot();
