<?php

namespace Roster\View;

class HtmlString
{
    /**
     * @var string
     */
    protected $html;

    /**
     * HtmlString constructor.
     * @param $html
     */
    public function __construct($html)
    {
        $this->html = $html;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->html;
    }
}
