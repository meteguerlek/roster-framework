<?php

namespace Roster\Sharp\Concerns;

trait CreateStatements
{
    /**
     * Create user statement
     *
     * @param $match
     * @param $options
     * @return string
     */
    protected function createStatement($match, $options)
    {
        if (array_key_exists('statement', $options) && array_key_exists('expression', $options))
        {
            $expression = $this->checkParam($options['expression'], $match);

            return "<?php {$options['statement']}({$expression}): ?>";
        }
        elseif (array_key_exists('statement', $options))
        {
            // If someone try to create 'else' statement help him
            if ($options['statement'] == 'else')
            {
                return "<?php {$options['statement']}: ?>";
            }

            return "<?php {$options['statement']}; ?>";
        }
    }

    /**
     * Check param
     *
     * @param $expression
     * @param $match
     * @return null|string|string[]|void
     */
    protected function checkParam($expression, $match)
    {
        if (preg_match('/{(.*?)}/', $expression))
        {
            if (isset($match[3]))
            {
                return preg_replace('/({(.*?)})/', $match[3], $expression);
            }

            throw new \Exception("$expression need parameter!");

            return;
        }

        return $expression;
    }

}