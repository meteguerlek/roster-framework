<?php

namespace Roster\Sharp\Concerns;

trait Strings
{
    /**
     * Echo
     *
     * @param $content
     * @return string
     */
    protected function e($content)
    {
        return "<?php echo {$content}; ?>";
    }

    /**
     * Hhtmlspecialchars
     *
     * @param $content
     * @return string
     */
    protected function hsc($content)
    {
        return "<?php echo e({$content}); ?>";
    }

    /**
     * Htmlentities
     *
     * @param $content
     * @return string
     */
    protected function he($content)
    {
        return "<?php echo htmlentities({$content}, ENT_QUOTES); ?>";
    }

    /**
     * For functions
     *
     * @param $content
     * @return string
     */
    protected function func($content)
    {
        return "<?php {$content}; ?>";
    }
}