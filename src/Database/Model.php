<?php

namespace Roster\Database;

use JsonSerializable;
use Roster\Support\Pluralizer;

class Model implements JsonSerializable
{
    /**
     * Put results from database
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Set table name
     *
     * @var null
     */
    protected $table = null;

    /**
     * Set hidden attributes
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Set update column and values
     *
     * @var array
     */
    protected $update = [];

    /**
     * Set where for delete or update
     *
     * @var string
     */
    protected $where = 'id';

    /**
     * @var null
     */
    protected $whereValue = null;

    /**
     * Get table name from model, if is null
     * tinker the name from model file name
     *
     * @return mixed|null
     */
    public function getTable()
    {
        if(!is_null($this->table))
        {
            return $this->table;
        }

        return $this->tableWithPlural();
    }

    /**
     * Set table
     *
     * @param $table
     * @return mixed
     */
    public function setTable($table)
    {
        return $this->table = $table;
    }

    /**
     * Get columns
     * @return array
     */
    public function getColumns()
    {
       return array_keys($this->attributes);
    }

    /**
     * Get where
     *
     * @return string
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * Set where
     *
     * @param $key
     * @return mixed
     */
    public function setWhere($key)
    {
        return $this->where = $key;
    }

    /**
     * Set where value
     *
     * @param $value
     * @return mixed
     */
    public function setWhereValue($value)
    {
        return $this->whereValue = $value;
    }

    /**
     * @param array $attributes
     * @return array
     */
    public function fill(array $attributes)
    {
        return $this->attributes += $attributes;
    }

    /**
     * Plural
     *
     * @return mixed
     */
    public function tableWithPlural()
    {
        $filter = explode('\\', get_class($this));

        $table = array_pop($filter);

        $table = Pluralizer::make($table);

        return $table;
    }

    /**
     * Get attribute, if isset
     *
     * @param $key
     * @return mixed|void
     */
    protected function getAttributes($key)
    {
        if(!$key)
        {
            return false;
        }

        if(array_key_exists($key, $this->attributes))
        {
            return $this->attributes[$key];
        }
/*
        if(method_exists($this, $key))
        {
            return $this->$key();
        }

        if (property_exists($this, $key))
        {
            return $this->{$key};
        }*/
    }

    /**
     * Set attribute
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    public function setAttributes($key, $value = null)
    {
        if (is_array($key))
        {
            return $this->attributes += $key;
        }

        $this->attributes[$key] = $value;

        return $this;
    }


    /**
     * Hide Attribute for API's
     *
     */
    public function hideAttributes()
    {
        foreach ($this->hidden as $hide)
        {
            unset($this->attributes[$hide]);
        }
    }

    /**
     * Save dynamically
     *
     * @return mixed
     */
    public function save()
    {
        return (new QueryBuilder($this))
            ->where($this->where, $this->whereValue)
            ->update($this->update);
    }

    /**
     * Delete dynamically
     *
     * @return \PDO
     */
    public function delete()
    {
        return (new QueryBuilder($this))
            ->where($this->where, $this->whereValue)
            ->delete();
    }

    /**
     * Check has
     *
     * @param string $key
     * @return bool
     */
    public function has($key = '')
    {
        if (array_key_exists($key, $this->attributes))
        {
            return true;
        }

        return false;
    }

    /**
     * Results to json also for API's
     *
     * @param bool $option
     * @return string
     */
    public function toJson($option = false)
    {
        return print_r(json_encode($this->jsonSerialize(), $option));
    }

    /**
     * The hide method is for json, but you can also
     * hide in arrays if you pass true as a parameter
     *
     * @param bool $hide
     * @return array
     */
    public function toArray($hide = false)
    {
        if ($hide)
        {
            $this->hideAttributes();
        }

        return $this->attributes;
    }

    /**
     * Unserialize
     *
     * @param $key
     * @param bool $here
     * @param null $to
     * @return Model
     */
    public function unserialize($key, $here = false, $to = null)
    {
        if (!isset($this->attributes[$key])) {
            return $this;
        }

        $unserialized = @unserialize($this->attributes[$key]);

        if ($unserialized === false || !is_array($unserialized) || empty($unserialized)) {
            return $this;
        }

        if ($here)
        {
            $this->fill($unserialized);
        }
        elseif (!is_null($to))
        {
            $this->attributes[$to] = $unserialized;
        }
        else
        {
            $this->attributes[$key] = $unserialized;
        }

        return $this;
    }

    /**
     * Results to array
     *
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return $this->toArray(true);
    }

    /**
     * Get attribute
     *
     * @param $key
     * @return mixed|void
     */
    public function __get($key)
    {
        return $this->getAttributes($key);
    }

    /**
     * Set attribute
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    public function __set($key, $value)
    {
        return $this->update[$key] = $value;
    }

    /**
     * Call query methods as non static
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    /*public function __call($name, $arguments)
    {
        return (new QueryBuilder($this))->{$name}(...$arguments);
    }*/

    /**
     * Call query methods as static
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return (new QueryBuilder(new static))->{$name}(...$arguments);
    }


}
