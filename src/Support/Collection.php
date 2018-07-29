<?php

namespace Roster\Support;

use ArrayIterator;
use Closure;
use JsonSerializable;
use IteratorAggregate;

class Collection implements IteratorAggregate, JsonSerializable
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var array
     */
    protected $with = [];

    /**
     * Make collection
     *
     * @param $items
     * @return static
     */
    public static function make($items)
    {
        $static = new static;

        $static->items = $items;

        return $static;
    }

    /**
     * With
     *
     * @param $with
     * @return $this
     */
    public function with($with)
    {
        $this->with += $with;

        return $this;
    }

    /**
     * Count items
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Get items
     *
     * @return mixed
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Get first item
     *
     * @return mixed
     */
    public function first()
    {
        return current($this->items);
    }

    /**
     * Get last item
     *
     * @return mixed
     */
    public function last()
    {
        return end($this->items);
    }

    /**
     * Get from index
     *
     * @param $index
     * @return mixed
     */
    public function index($index)
    {
        return isset($this->items[$index])
            ? $this->items[$index]
            : false;
    }

    /**
     * Array map
     *
     * @param Closure $callback
     * @return array
     */
    public function map(Closure $callback)
    {
        return array_map($callback, $this->items);
    }

    /**
     * Push
     *
     * @param $content
     * @return $this
     */
    public function push($content)
    {
        $this->items[] = $content;

        return $this;
    }

    /**
     * Put content with key and value
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function put($key, $value)
    {
        $this->items[$key] = $value;

        return $this;
    }

    /**
     * Random item
     *
     * @return bool|mixed
     */
    public function random()
    {
        if (empty($this->items))
        {
            return false;
        }

        $key = array_rand($this->items);

        return $this->items[$key];
    }

    /**
     * Shuffle items
     *
     * @return $this
     */
    public function shuffle()
    {
        shuffle($this->items);

        return $this;
    }

    /**
     * Slice items
     *
     * @param $offset
     * @return $this
     */
    public function slice($offset)
    {
        $this->items = array_slice($this->items, $offset);

        return $this;
    }

    public function sort()
    {
        sort($this->items);

        return $this;
    }

    /**
     * Filter items
     *
     * @param array ...$keys
     * @return array
     */
    public function filter(...$keys)
    {
        $filter = [];

        foreach ($this->items as $item)
        {
            foreach ($keys as $key)
            {
                $filter[] = $item->{$key};
            }
        }

        return $filter;
    }

    /**
     * Pluck items
     *
     * @param $key
     * @param $value
     * @return array
     */
    public function pluck($key, $value = false)
    {
        $pluck = [];

        if ($value)
        {
            foreach ($this->items as $item)
            {
                $pluck[$item->{$key}] = $item->{$value};
            }
        }
        else
        {
            foreach ($this->items as $item)
            {
                $pluck[] = $item->{$key};
            }
        }

        return $pluck;
    }

    /**
     * Is empty
     *
     * @return bool
     */
    public function empty()
    {
        return empty($this->items);
    }

    /**
     * Items to json
     *
     * @param bool $option
     * @return string
     */
    public function toJson($option = true)
    {
       return json_encode($this->jsonSerialize(), $option);
    }

    /**
     * Json Serialize
     *
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return array_map(function ($item){

            if ($item instanceof JsonSerializable)
            {
                return $item->jsonSerialize();
            }

            return $item;

        }, $this->items);
    }

    /**
     * Items to array
     *
     * @param bool $hide
     * @return array
     */
    public function toArray($hide = false)
    {
        return array_map(function ($item) use ($hide){

            if ($item instanceof JsonSerializable)
            {
                return $item->toArray($hide);
            }

            return $item;

        }, $this->items);
    }

    /**
     * Array Iterator
     *
     * @return ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Get from with
     *
     * @param $key
     * @return bool|mixed
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->with))
        {
            return $this->with[$key];
        }

        return false;
    }

    /**
     * Get items from $with
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        foreach ($this->with as $key => $object)
        {
            if (method_exists($this->with[$key], $name))
            {
                return $this->with[$key]->{$name}(...$arguments);
            }
        }
    }
}