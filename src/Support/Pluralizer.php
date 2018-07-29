<?php

namespace Roster\Support;

class Pluralizer
{

    /**
     * @var array
     */
    protected $irregularPlurals = [
        'child' => 'children'
    ];

    /**
     * @var array
     */
    protected $rules = [
        'ies' => 'y',
        'oes' => 'o',
        'ves' => ['f', 'fe'],
        'es' => ['s', 'x', 'ch', 'sh']
    ];

    /**
     * @var array
     */
    protected $pluralWithoutDrop = [
        's', 'x', 'ch', 'sh'
    ];

    /**
     * Pluralize string
     *
     * @param string $model
     * @param bool $original
     * @return mixed|string
     */
    protected function make(string $model, $original = false)
    {
        if (in_array($model, array_keys($this->irregularPlurals)))
        {
            return $model = $this->irregularPlurals[$model];
        }

        if (!$original)
        {
            $model = $this->parse($model);
        }

        foreach ($this->rules as $plural => $endings)
        {
            if (is_array($endings))
            {
                foreach ($endings as $ending)
                {
                    if ($model[strlen($model) - 1] == $ending) // Last letter
                    {
                        return $this->find($model, $plural, $ending);
                    }
                }

                continue;
            }

            if ($model[strlen($model) - 1] == $endings) // Last letter
            {
                return $this->find($model, $plural, $endings);
            }
        }

        $model .= 's';

        return $model;
    }

    /**
     * Find plural
     *
     * @param $model
     * @param $plural
     * @param $ending
     * @return string
     */
    protected function find($model, $plural, $ending)
    {
        if (in_array($ending, $this->pluralWithoutDrop))
        {
            $model .= $plural;

            return $model;
        }

        return substr($model, 0, - strlen($ending)) . $plural;
    }

    /**
     * Parse table name
     *
     * @param $model
     * @return string
     */
    protected function parse($model)
    {
        $parse = '';

        $splits = str_split($model);

        foreach ($splits as $key => $split)
        {
            // Skip first letter
            if ($key !== 0)
            {
                // Check if the letter is capitalized
                if (ctype_upper($split))
                {
                    // If true set underlined and the letter lower
                    $parse .= '_'. strtolower($split);
                }
                else
                {
                    // Continue
                    $parse .= $split;
                }
            }
            else
            {
                // Continue
                $parse .= $split;
            }
        }

        // Return lower
        return strtolower($parse);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return (new static)->{$name}(...$arguments);
    }
}