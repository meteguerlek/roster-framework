<?php

namespace Roster\Support\Image;

use Roster\Filesystem\File;

class Img
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var null
     */
    protected $directory = null;

    /**
     * @var null
     */
    protected $fileName = null;

    /**
     * @var null
     */
    protected $fileType = null;

    /**
     * @var bool
     */
    protected $error = false;

    /**
     * @var null
     */
    protected $missing = null;

    /**
     * @var array
     */
    protected $methods = [
        'load', 'update', 'create'
    ];

    /**
     * Set file path
     *
     * @param $directory
     * @param $fileName
     * @param $fileType
     * @return static
     */
    protected function handler($directory, $fileName, $fileType)
    {
        $static = (new static);

        $static->directory = $directory;
        $static->fileName = $fileName;
        $static->fileType = $fileType;
        $static->options['type'] = $fileType;

        return $static;
    }


    protected function crop()
    {
        $this->hasOptions('width', 'height');

        $orginal = File::where($this->directory, $this->fileName, $this->fileType)->getPath();

        $orginal = @imagecreatefrompng($orginal);

        $croped = @imagecrop($orginal, [
            'x' => 0,
            'y' => 0,
            'width' => $this->getOption('width'),
            'height' => $this->getOption('height')
        ]);

        if ($croped)
        {
            if ($this->hasOption('name'))
            {
                $path = File::where($this->directory, $this->getOption('name'), $this->fileType)->getPath();

                @imagepng($croped, $path);
            }
            else
            {
                // TODO if file exist change file name
                $path = File::where($this->directory, $this->fileName. '_croped', $this->fileType);

                @imagepng($croped, $path);
            }

            $this->options['path'] = $path;
        }
        else
        {
            $this->error = true;
        }

        return $this;
    }

    protected function save()
    {
        $this->hasOptions('width', 'height', 'text', 'text_color', 'font', 'font_width', 'font_height'); #

        $im = @imagecreatetruecolor($this->getOption('width'), $this->getOption('height'));

        if (preg_match('/^#?([0-9a-f]{6}|[0-9a-f]{3})$/', '#'.$this->getOption('text_color')))
        {
            $hex = '#'.$this->getOption('text_color');

            list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");

            $text_color = imagecolorallocate($im, $r, $g, $b);
        }
        elseif (preg_match('/^(rgb)?\(?([01]?\d\d?|2[0-4]\d|25[0-5])(\W+)([01]?\d\d?|2[0-4]\d|25[0-5])\W+(([01]?\d\d?|2[0-4]\d|25[0-5])\)?)$/', $this->getOption('text_color'),$match))
        {
            list($r, $g, $b) = array_map('trim', explode(',', $match[0]));

            $text_color = imagecolorallocate($im, $r, $g, $b);
        }
        else
        {
            $text_color = 15273563;
        }

        imagestring($im, $this->getOption('font'), $this->getOption('font_width'), $this->getOption('font_height'),  $this->getOption('text'), $text_color);

        $save = File::where($this->directory, $this->fileName, $this->fileType)->getPath();

        imagepng($im, $save);

        return $this;
    }

    /**
     * Check convert failed
     *
     * @return bool
     */
    public function hasErrors()
    {
        return $this->error;
    }

    /**
     * Check if options exist
     *
     * @param array ...$options
     */
    public function hasOptions(...$options)
    {
        if (is_array($options))
        {
            foreach ($options as $option)
            {
                if (!array_key_exists($option, $this->options))
                {
                    throw new \Exception("Option $option needed");
                    return;
                }
            }
        }
    }

    /**
     * Check if otion exist
     *
     * @param $name
     * @return bool
     */
    protected function hasOption($name)
    {
        return array_key_exists($name, $this->options)
            ? true
            : false;
    }

    /**
     * Get option
     *
     * @param $name
     * @return mixed|null
     */
    protected function getOption($name)
    {
        return array_key_exists($name, $this->options)
            ? $this->options[$name]
            : null;
    }

    /**
     * Get options
     *
     * @return mixed|null
     */
    protected function getOptions()
    {
        return $this->options;
    }

    /**
     * Get option
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->options[$name];

    }

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this, $name))
        {
            return $this->{$name}(...$arguments);
        }

        throw new \Exception("Method {$name} not exist");
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        $static = new static;

        if (in_array($name, $static->methods))
        {
            return $static->handler(...$arguments);
        }

        if (method_exists($static, $name))
        {
            return $static->{$name}(...$arguments);
        }

        throw new \Exception("Method {$name} not exist");
    }

}
