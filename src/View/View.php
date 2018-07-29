<?php

namespace Roster\View;

use Roster\Filesystem\File;
use Roster\Sharp\LayoutBuilder;
use Roster\Sharp\SharpCompiler;

class View
{
    /**
     * @var string
     */
    protected $directory;

    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var string
     */
    protected $fileType;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var array
     */
    protected static $variables = [];

    /**
     * View constructor.
     *
     * @param $template
     * @param array $variables
     * @param null $directory
     */
    public function __construct($template, $variables = [], $directory = null)
    {
        $this->template = $template;

        static::$variables += $variables;

        $this->directory = is_null($directory) ? config('disk.view') : $directory;

        $this->render();
    }

    /**
     * Get file from views
     *
     * @return null|String
     */
    public function getView()
    {
        $file = File::where($this->directory, $this->template, 'php')->getPath();

        if ($checkSharp = $this->isSharp())
        {
            static::$variables += ['__sharp' => new LayoutBuilder()];

            return (new SharpCompiler())->compile($checkSharp, $this->template);
        }

        return $file;
    }

    /**
     * Render view
     *
     * @return mixed
     */
    public function render()
    {
        $file = $this->getView();

        extract(static::$variables);

        return include $file;
    }

    /**
     * @return string
     */
    public function html()
    {
        $file = $this->getView();

        extract(static::$variables);

        ob_start();

        include $file;

        return ob_get_clean();
    }

    /**
     * Check if view is sharp
     *
     * @return null|String
     */
    public function isSharp()
    {
        $file = File::where($this->directory, $this->template, 'sharp.php');

        return $file->exist() ? $file->getPath() : false;
    }

    public static function share($key, $value)
    {
        static::$variables[$key] = $value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '';
    }
}