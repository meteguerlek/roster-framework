<?php

namespace Roster\Filesystem;

use Roster\Support\App;

class Disk
{
    /**
     * Make dir
     *
     * @param $path
     * @param $mode
     * @return bool
     */
    public static function makeDir($path, $mode = 0700)
    {
        $filter = explode('.', $path);

        return mkdir(ABSPATH.'/'.implode('/', $filter), $mode);
    }

    public static function scanDir($path)
    {
        $filter = explode('.', $path);

        return scandir(ABSPATH.'/'.implode('/', $filter));
    }
}
