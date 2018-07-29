<?php

namespace Roster\Filesystem;

use Roster\Support\App;
use Roster\Support\Str;

class Upload
{
    /**
     * @var array
     */
    protected $file;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $extension;

    /**
     * @var string
     */
    protected $realPath;

    /**
     * @var string
     */
    protected $uploadPath;

    /**
     * Upload file
     *
     * @param $file
     * @return string|void
     */
    public static function file($file)
    {
        $static = new static;

        if (is_object($file))
        {
            $static->file = (array) $file;
        }
        elseif (empty($file))
        {
            throw new \Exception("The contents are empty. The file could not be uploaded.");
            return;
        }

        return $static;
    }

    /**
     * Set filename and extension
     *
     * @param $name
     * @param string $extension
     * @return $this
     */
    public function storeAs($name, $extension = '')
    {
        $this->name = $name;

        $this->extension = $extension;

        return $this;
    }

    /**
     * Set directory
     *
     * @param $path
     * @return $this
     */
    public function where($path)
    {
        $this->uploadPath = 'files/'.$path;

        $path = config('disk.storage.files').'.'.$path;

        $path = str_replace('.', '/', $path);

        $this->realPath = $path;

        return $this;
    }

    /**
     * Move file
     *
     * @return string
     */
    public function save()
    {
        $this->checkFileDatas();

        $this->checkPath();

        move_uploaded_file($this->file['tmp_name'], ABSPATH.'/'.$this->realPath.'/'.$this->name.'.'.$this->extension);

        return $this->uploadPath.'/'.$this->name.'.'.$this->extension;
    }

    /**
     * Check file datas
     *
     * @return $this
     */
    protected function checkFileDatas()
    {
        if (!$this->name)
        {
            $this->name = now('Y-m-d').Str::rand(10);
        }

        if (!$this->extension)
        {
            $this->extension = pathinfo($this->file['name'], PATHINFO_EXTENSION);
        }

        if (!$this->realPath)
        {
            $this->realPath = str_replace('.', '/', config('disk.storage.files'));;
        }

        return $this;
    }

    /**
     * Check path
     *
     * @return $this
     */
    protected function checkPath()
    {
        if (!is_dir(ABSPATH.'/'.$this->realPath))
        {
            $folders = explode('/', $this->realPath);
            $paths = '';

            foreach ($folders as $folder)
            {
                $paths .= '/'.$folder;
                mkdir(ABSPATH.'/'.$paths);
            }
        }

        return $this;
    }
}