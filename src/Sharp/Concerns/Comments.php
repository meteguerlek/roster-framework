<?php

namespace Roster\Sharp\Concerns;

trait Comments
{
    /**
     * Comment
     *
     * @param $content
     * @return string
     */
    protected function com($content)
    {
        return "<!-- $content -->\n";
    }
}