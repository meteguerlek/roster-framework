<?php

namespace Roster\Debugger;

class Console
{
    /**
     * Put logs
     * 
     * @var array 
     */
    protected $logs = [];

    /**
     * Writes an error message to the console as soon as
     * the claim is incorrect. If the claim is true, nothing happens.
     *
     * @param $assertion
     * @param $log
     * @return Command
     */
    protected function assert($assertion, $log)
    {
        if (!$assertion)
        {
            $this->logs['assert'] = [
                'assertion' => $assertion,
                'log' => $log
            ];

            return $this->save();
        }
    }

    /***
     * Clears the console.
     *
     */
    protected function clear()
    {
        $file = '../storage/app/logs/console.php';

        file_put_contents($file, '');
    }

    /**
     * Outputs an error message to the Console.
     *
     * @param $result
     * @return Command
     */
    protected function error($result)
    {
        $this->logs['error'] = $result;

        return $this->save();
    }

    /**
     * Creates a new inline group in the Console.
     * This indents following console messages by an
     * additional level, until groupEnd() is called.
     *
     * @param $result
     * @return Command
     */
    protected function group($result)
    {
        $this->logs['group'] = $result;

        return $this->save();
    }

    /**
     * Close group
     *
     * @param string $result
     * @return Command
     */
    protected function groupEnd($result = '')
    {
        $this->logs['groupEnd'] = $result;

        return $this->save();
    }

    /**
     * Outputs an informational message to the Console.
     *
     * @param $log
     * @return Command
     */
    protected function info($log)
    {
        $this->logs['info'] = $log;

        return $this->save();
    }

    /**
     * Outputs a message to the Console.
     *
     * @param $logs
     * @return Command
     */
    protected function log($logs)
    {
        $logList = func_get_args();

        $this->logs['log'] = $logList;

        return $this->save();
    }

    /**
     * Show query
     *
     * @param $logs
     * @return Command
     */
    protected function query($logs)
    {
        $this->logs['query'] = $logs;

        return $this->save();
    }

    /**
     * Displays tabular data as a table.
     *
     * @param $table
     * @return array
     */
    protected function table($table)
    {
        if (empty($table->count()))
        {
            return self::error('Invalid Array');
        }

        $column = [];

        $rows = [];

        foreach ($table->toArray() as $tables)
        {
            foreach ($tables as $col => $row)
            {
                if (count($column) <= count($tables))
                {
                    $column[] = $col;
                }

                $rows[] = $row;
            }
        }

        if(empty($column) && empty($rows))
        {
            return $this->logs['table'] = [];
        }

        $this->logs['table'] = [
            'rows' => $rows,
            'column' => $column,
            'columnNumber' => count($column) - 1,
            'date' => $this->getTime()
        ];

        return $this->save(false);
    }

    /**
     * Outputs a warning message to the Console.
     *
     * @param $result
     * @return Command
     */
    protected function warn($result)
    {
        $this->logs['warn'] = $result;

        return $this->save();
    }

    protected function getTime()
    {
        $date = new \DateTime();

        return $date->format('U');
    }

    /**
     * Save logs
     *
     * @param bool $date
     * @return Command
     */
    protected function save($date = true)
    {
        if ($date)
        {
            $this->logs['date'] = $this->getTime();
        }

        // get key from current log
        $method = key($this->logs);

        // save new Command
        return new Command($method, $this->logs);
    }

    public static function __callStatic($name, $arguments)
    {
        if (method_exists(new static, $name))
        {
            return (new static)->{$name}(...$arguments);
        }
        
        throw new \Exception("Method {$name} not exist");
    }

}
