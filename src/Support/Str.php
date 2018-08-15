<?php

namespace Roster\Support;

class Str
{
    /**
     * Generate random string
     *
     * @param int $max
     * @param array $options
     * @return string
     */
    public static function rand($max = 10, array $options = [])
    {
        if (empty($options))
        {
            $alphas = array_merge(
                range('A', 'Z'),
                range('a', 'z'),
                range(0, 9)
            );
        }
        else
        {
            $alphas = [];

            foreach ($options as $low => $high)
            {
                $alphas = array_merge($alphas, range($low, $high));
            }
        }

        // Mix mix
        shuffle($alphas);

        $random = '';

        for ($i = 0; $i < $max; $i++)
        {
            $random .= $alphas[$i];
        }

        return $random;
    }

    /**
     * Replace string
     *
     * @param array $search
     * @param $str
     * @return bool|mixed
     */
    public static function replace(array $search, $str)
    {
        foreach ($search as $s => $r)
        {
            $str = str_replace($s, $r, $str);
        }

        return $str;
    }

    /**
     * Convert string to slug
     *
     * @param $str
     * @param string $split
     * @return null|string|string[]
     */
    public static function slug($str, $split = '-')
    {
        // replace non letter or digits by -
        $str = preg_replace('~[^\pL\d]+~u', $split, $str);

        // transliterate
        $str = iconv('utf-8', 'us-ascii//TRANSLIT', $str);

        // remove unwanted characters
        $str = preg_replace('~[^-\w]+~', '', $str);

        // trim
        $str = trim($str, $split);

        // remove duplicate -
        $str = preg_replace('~-+~', $split, $str);

        // lowercase
        $str = strtolower($str);

        if (empty($str)) {
            return 'n-a';
        }

        return $str;
    }

    /**
     * Check if string contains word
     *
     * @param string $str
     * @param string $has
     * @return string
     */
    public static function has(string $str, string $has)
    {
        return strstr($str, $has);
    }

    /**
     * Singular to plural
     *
     * @param string $str
     * @return mixed|string
     */
    public static function plural(string $str)
    {
        return Pluralizer::make($str, true);
    }
    
}