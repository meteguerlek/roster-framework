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
     * @var string
     */
    protected $pluralizered;

    /**
     * Pluralize string
     *
     * @param string $string
     * @param bool $original
     * @return mixed|string
     */
    public function __construct(string $string, $original = false)
    {
        if (in_array($string, array_keys($this->irregularPlurals)))
        {
            return $this->pluralizered = $this->irregularPlurals[$string];
        }

        if (!$original)
        {
            $string = $this->parse($string);
        }

        foreach ($this->rules as $plural => $endings)
        {
            if (is_array($endings))
            {
                foreach ($endings as $ending)
                {
                    if ($string[strlen($string) - 1] == $ending) // Last letter
                    {
                        return $this->pluralizered = $this->find($string, $plural, $ending);
                    }
                }

                continue;
            }

            if ($string[strlen($string) - 1] == $endings) // Last letter
            {
                return $this->pluralizered = $this->find($string, $plural, $endings);
            }
        }

        $this->pluralizered = $string.'s';;
    }

    /**
     * @param string $string
     * @param bool $original
     * @return Pluralizer
     */
    public static function make(string $string, $original = false)
    {
        return (new static($string, $original))->pluralizered;
    }

    /**
     * @return string
     */
    public function getPluralizered()
    {
        return $this->pluralizered;
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
}