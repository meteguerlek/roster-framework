<?php

namespace Roster\Logger;

use Roster\Filesystem\File;

class Log
{
    /**
     * Log types
     *
     * @var array
     */
    protected $types = [
        'error', 'success', 'info', 'warning'
    ];

    /**
     * Log
     *
     * @param $type
     * @param $content
     * @return bool|null
     * @throws \Exception
     */
    protected function add($type, $content)
    {
        if (!config('app.logger'))
        {
            return false;
        }

        $content = is_array($content) ? json_encode($content) : $content;

        if (is_array($content) || is_object($content))
        {
            $content = json_encode((array) $content);
        }

        $content = '['. date('Y-m-d H:i:s') . '] '.$type.': '.$content;

        return File::create($content."\n", 'storage.framework.logs', 'app', [
            'filetype' => 'log',
            'mode' => 'a'
        ]);
    }

    /**
     * @param $name
     * @param $arguments
     * @return bool|null
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        $static = new static;

        if (in_array($name, $static->types) && isset($arguments[0]))
        {
            return $static->add($name, $arguments[0]);
        }
    }
}
