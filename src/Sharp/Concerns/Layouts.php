<?php

namespace Roster\Sharp\Concerns;

trait Layouts
{
    /**
     * Put sections
     *
     * @var array
     */
    protected $sections = [];

    /**
     * Mathed sections
     *
     * @var null
     */
    protected $matchedSections = null;

    /**
     * Extend layout
     *
     * @param $layout
     * @return string
     */
    protected function extends($layout)
    {
        return "<?php \$__sharp->layout{$layout}; ?>";
    }

    /**
     * Show stored data
     *
     * @param $section
     * @return string
     */
    protected function yield($section)
    {
        return "<?php echo \$__sharp->yieldContent{$section}; ?>";
    }

    /**
     * Set sections
     *
     * @param $section
     * @return string
     */
    protected function section($section)
    {
        $this->sections[] = $section;

        return "<?php \$__sharp->startSection{$section}; ?>";
    }

    /**
     * End section
     *
     * @return string
     */
    protected function endsection()
    {
        $endSection = "<?php \$__sharp->endSection(); ?>";

        // Is last section
        if (count($this->sections) == count($this->matchedSections[0]))
        {
            $endSection .= "<?php \$__sharp->make(); ?>";
        }

        return $endSection;
    }
}
