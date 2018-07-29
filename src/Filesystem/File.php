<?php

namespace Roster\Filesystem;

use Roster\Support\App;
use Roster\Support\Str;

class File
{
    /**
     * @var null
     */
    protected $file = null;

    /**
     * Options
     *
     * @var array
     */
    protected static $customOptions = [
        'mode' => 'w',
        'filetype' => 'php'
    ];

    /**
     * Set location
     *
     * @param $directory
     * @param bool $fileName
     * @param string $fileType
     * @return static
     */
    public static function where($directory, $fileName = false, $fileType = 'php')
    {
        $static = new static;

        $static->file = !$fileName ? $directory : $static->compilePath($directory, $fileName, $fileType);

        return $static;
    }

    /**
     * Compile path
     *
     * @param $directory
     * @param $fileName
     * @param $fileType
     * @return string
     */
    protected function compilePath($directory, $fileName, $fileType)
    {
        // Explode dot
        $directory = explode('.', $directory);

        // Explode too dot from filename if exist
        $fileName = explode('.', $fileName);

        // Merge the two arrays
        $directory = array_merge($directory, $fileName);

        // Filename is always the last index
        $fileName = array_pop($directory);

        // Convert directory
        $directory = (!empty($directory[0])) ? implode('/', $directory) . '/' : array_pop($directory);

        return ABSPATH. '/' .$directory .$fileName. '.' .$fileType;
    }

    /**
     * Create file
     *
     * @param $content
     * @param $directory
     * @param $fileName
     * @param array $options
     * @return null
     */
    public static function create($content, $directory, $fileName, $options = [])
    {
        // Merge Custom options and User options
        $options = array_replace(static::$customOptions, $options);

        // Filepath
        $path = static::where($directory, $fileName, $options['filetype'])->getPath();

        // Open or create file
        $file = fopen($path, $options['mode']);

        // Put content
        fwrite($file, $content);

        // Close file
        fclose($file);

        // return the filepath
        return $path;
    }

    /**
     * Check dir
     *
     * @param $path
     * @return bool
     */
    public static function isDir($path)
    {
        return is_dir(ABSPATH.'/'.Str::replace(['.' => '/'], $path))
            ? true
            : false;
    }

    /**
     * Make dir
     *
     * @param $path
     * @return bool
     */
    public static function makeDir($path)
    {
        return mkdir(ABSPATH.'/'.Str::replace(['.' => '/'], $path));
    }

    /**
     * Get path
     *
     * @return null
     */
    public function getPath()
    {
        return $this->file;
    }

    /**
     * Delete file
     *
     * @return bool
     */
    public function delete()
    {
        return unlink($this->file);
    }

    /**
     * Get file content
     *
     * @return bool|string
     */
    public function getContent()
    {
        return file_get_contents($this->file);
    }

    /**
     * Check if file exist
     *
     * @return bool
     */
    public function exist()
    {
        // Check if file exist
        return file_exists($this->file)
            ? true
            : false;
    }

    /**
     * Get filemtime
     *
     * @return bool|int
     */
    public function lastModified()
    {
        return filemtime($this->file);
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return filesize($this->file);
    }
}
