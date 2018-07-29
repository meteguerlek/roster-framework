<?php

namespace Roster\Debugger;

use Roster\Filesystem\File;

class Command
{
    /**
     * @var null
     */
    public $logs = null;

    /**
     * Command constructor.
     * @param $method
     * @param $logs
     */
    public function __construct($method, $logs)
    {
        $this->show($method, $logs);
    }

    /**
     * @param $method
     * @param $logs
     */
    public function show($method, $logs)
    {
        // Generate log html
        $getHtml = '<div class="console-result">
                        <i class="fa fa-angle-left" aria-hidden="true"></i>
                    '. $this->{$method}($logs) .
                    '</div>';

        // Parse HTML
        $getHtml = $this->htmlParse($getHtml);

        // Get logs file
        $dir = config('disk.storage.logs');

        // File name
        $fileName = 'console';

        // Set HTML
        $content = $getHtml."\n";

        $options = [
            'mode' => 'a+'
        ];

        File::create($content, $dir, $fileName, $options);

    }

    /**
     * @param $logs
     * @return string
     */
    public function assert($logs)
    {
        $html = '
        <div class="assert">
              Assertion failed: '. $logs['assert']['log'] .'
        </div>';

        return $html;
    }

    /**
     * @param $logs
     * @return string
     */
    public function error($logs)
    {
        $html = '
        <div class="error">
             '. $logs['error'] .'
        </div>';

        return $html;
    }

    /**
     * @param $logs
     * @return string
     */
    public function group($logs)
    {
        $html = '
        <div class="group group-default">
            <div class="group-heading">
                <button type="button" class="group-btn">'. $logs['group'] .'<i class="fa fa-caret-right" aria-hidden="true"></i></button>
            </div>
            <div class="group-collapse">
                <div class="group-body">';

        return $html;
    }

    /**
     * @return string
     */
    public function groupEnd()
    {
        $html = '
                </div>
            </div>
        </div>';

        return $html;
    }

    /**
     * @param $logs
     * @return string
     */
    public function info($logs)
    {
        $html = '
        <div class="info">
            '. $logs['info'] .'
        </div>';

        return $html;
    }

    /**
     * @param $logs
     * @return string
     */
    public function log($logs)
    {
        $html = '';

        foreach ($logs['log'] as $log)
        {
            if (is_array($log))
            {
                $html .= '
                <div class="log">
                    '. print_r($log) .'
                </div>';
            }
            else
            {
                $html .= '
                <div class="log">
                    '. $log .'
                </div>';
            }

        }

        return $html;
    }

    /**
     * @param $logs
     * @return string
     */
    public function query($logs)
    {
        $result = $logs['query']->queryString;
        $error = $logs['query']->errors[2];

        if (!empty($error))
        {
            $log['error'] = $error;

            return $this->error($log);
        }

        $html = '
        <div $result="query">
            '. $result .'
        </div>';

        return $html;
    }

    /**
     * @param $logs
     * @return string
     */
    public function table($logs)
    {
        if(empty($logs['table']))
        {
            $logs['error'] = 'Table is empty!';

            return $this->error($logs);
        }

        $column = [];

        $rows = [];

        foreach ($logs as $table)
        {
            for ($i = 0; $i < $table['columnNumber']; $i++)
            {
                $column[] = '<th>'. $table['column'][$i] .'</th>'; // thead results
            }

            foreach (array_chunk($table['rows'], $table['columnNumber']) as $diff)
            {
                $rows[] = '<tr>';

                for ($i = 0; $i < count($diff); $i++)
                {
                    $rows[] = '<td>'. $diff[$i] .'</td>'; // tbody results

                }
                $rows[] = '</tr>';

            }
        }

        $html = '
        <table class="console-table"><thead>
            <tr>
                '. implode('', $column) .'
            </tr>
            </thead>
            <tbody>
                '. implode('', $rows) .'
            </tbody>
        </table>';

        return $html;
    }

    /**
     * @param $logs
     * @return string
     */
    public function warn($logs)
    {
        $html = '
        <div class="warn">
            '. $logs['warn'] .'
        </div>';

        return $html;
    }

    /**
     * @param $html
     * @return null|string|string[]
     */
    public function htmlParse($html)
    {
        $parse = preg_replace("/\r|\n\s+/", "", $html);

        return $parse;
    }

}
