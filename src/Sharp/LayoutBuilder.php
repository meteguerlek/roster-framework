<?php

namespace Roster\Sharp;

use Roster\View\View;

class LayoutBuilder
{
    protected $layout = null;

    protected $contents = [];

    protected $currentSection = null;

    public function layout($layout)
    {
        $this->layout = $layout;
    }

    public function startSection($name, $value = false)
    {
        if ($value)
        {
            return $this->contents[$name] = $value;
        }

        ob_start();

        $this->currentSection = $name;
    }

    public function endSection()
    {
        $this->contents[$this->currentSection] = ob_get_clean();
    }

    public function make()
    {
        return new View($this->layout, ['__sharp' => $this]);
    }

    public function yieldContent($name)
    {
        return isset($this->contents[$name]) ? $this->contents[$name] : false;
    }
}
