<?php

namespace Roster\Sharp\Concerns;

use Roster\Filesystem\File;
use Roster\Sharp\SharpCompiler;

trait Statements
{
    /**
     * @param $name
     * @param $content
     * @return string
     */
    protected function head($name, $content)
    {
        return "<?php {$name}{$content}: ?>";
    }

    /**
     * @param $name
     * @return string
     */
    protected function footer($name)
    {
        return "<?php {$name}; ?>";
    }

    /**
     * @param $name
     * @param $content
     * @return string
     */
    protected function alone($name, $content)
    {
        return "<?php {$name}{$content}; ?>";
    }

    /**
     * @param $name
     * @param $path
     * @return string
     */
    public function render($name, $path)
    {
        // Without quote
        $parsePath = $this->withoutQuote($path);

        // Get file path to check if it sharp
        $file = File::where(config('disk.view'), $parsePath, 'sharp.php')->getPath();

        // Check sharp
        if (File::where($file)->exist())
        {
            return "<?php (new \Roster\Sharp\SharpCompiler())->makeLayout($path); ?>";
        }

        return "<?php {$name}(File::where(config('disk.view'), {$path})->getPath()); ?>";
    }
}