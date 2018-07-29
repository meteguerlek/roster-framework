<?php

namespace Roster\Sharp\Concerns;

trait customCompiler
{
    /**
     * Php
     *
     * @return string
     */
    protected function customCompilerPhp()
    {
        return "<?php";
    }

    /**
     * End Php
     *
     * @return string
     */
    protected function customCompilerEndphp()
    {
        return "?>";
    }

    /**
     * Else
     *
     * @return string
     */
    protected function customCompilerElse()
    {
        return "<?php else: ?>";
    }

    /**
     * Go to
     *
     * @param $content
     * @return bool|string
     */
    protected function customCompilerGoto($content)
    {
        if (!isset($content[3]))
        {
            throw new \Exception('goto statement needed instruction to jump. Forexample: goto(instruction)');

            return false;
        }

        return "<?php goto {$content[4]} ?>";
    }

    /**
     * End go to
     *
     * @param $content
     * @return string
     */
    protected function customCompilerEndgoto($content)
    {
        return "<?php {$content[4]}: ?>";
    }

    /**
     * Do while
     *
     * @param $content
     * @return bool|string
     */
    protected function customCompilerDo($content)
    {
        if (!isset($content[3]))
        {
            throw new \Exception('do statement needed expression. Forexample: do(expression)');

            return false;
        }

        return "<?php $content[4]; do { ?>";
    }

    /**
     * End do while
     *
     * @param $content
     * @return bool|string
     */
    protected function customCompilerEnddowhile($content)
    {
        if (!isset($content[3]))
        {
            throw new \Exception('while statement needed expression. Forexample: while(expression)');

            return false;
        }

        return "<?php } while ({$content[4]}); ?>";
    }

    /**
     * Auth check
     *
     * @return string
     */
    protected function customCompilerAuth()
    {
        return "<?php if (Auth::check()): ?>";
    }

    /**
     * End auth check
     *
     * @return string
     */
    protected function customCompilerEndauth()
    {
        return "<?php endif; ?>";
    }

    /**
     * Guest check
     *
     * @return string
     */
    protected function customCompilerGuest()
    {
        return "<?php if (!Auth::check()): ?>";
    }

    /**
     * End guest check
     *
     * @return string
     */
    protected function customCompilerEndguest()
    {
        return "<?php endif; ?>";
    }

    /**
     * Class
     *
     * @param $content
     * @return string
     */
    protected function customCompilerClass($content)
    {
        $filter = explode(', ', $content[4]);

        list($variable, $class) = $filter;

        return "<?php $variable = new \\$class; ?>";
    }

    /**
     * Csrf
     *
     * @return string
     */
    protected function customCompilerCsrf()
    {
        return "<?php echo csrf(); ?>";
    }

    /**
     * Route
     *
     * @param $content
     * @return string
     */
    protected function customCompilerRoute($content)
    {
        return isset($content[3])
            ? "<?php echo url(route$content[3]); ?>"
            : "";
    }

    /**
     * Input
     *
     * @param $content
     * @return string
     */
    protected function customCompilerOld($content)
    {
        return isset($content[3])
            ? "<?php echo old{$content[3]}; ?>"
            : "";
    }

    /**
     * Url
     *
     */
    protected function customCompilerUrl($content)
    {
        return isset($content[3])
            ? "<?php echo url{$content[3]}; ?>"
            : "";
    }
}