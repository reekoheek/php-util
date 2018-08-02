<?php

namespace ROH\Util;

use Iterator;
use Countable;
use ArrayAccess;
use JsonKit\JsonSerializer;

class Collection implements ArrayAccess, Iterator, Countable, JsonSerializer
{
    /**
     * Attributes of document.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Constructor
     *
     * @method __construct
     *
     * @param mixed $attributes
     */
    public function __construct($attributes = null)
    {
        if ($attributes !== null) {
            if ($attributes instanceof Collection) {
                $attributes = $attributes->attributes;
            }

            $this->attributes = $attributes;
        }
    }

    /**
     * Get the value of attributes based on offset.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->offsetExists($key) ? $this->attributes[$key] : null;
    }

    /**
     * Set a value of an attributes.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Determine if attribute exist by the offset name.
     *
     * @param string $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Remove an attributes value by the offset name.
     *
     * @param string $key
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->attributes[$key]);
    }

    /**
     * Get current item in attributes.
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->attributes);
    }

    /**
     * Get next item in attributes.
     *
     * @return function [description]
     */
    public function next()
    {
        return next($this->attributes);
    }

    /**
     * Get keys of attributes.
     *
     * @return array
     */
    public function key()
    {
        return key($this->attributes);
    }

    /**
     * Determine if current loop has an items.
     *
     * @return mixed
     */
    public function valid()
    {
        return null !== $this->key();
    }

    /**
     * Rewind the cursor to the first items in haystack.
     *
     * @return mixed
     */
    public function rewind()
    {
        return reset($this->attributes);
    }

    /**
     * Get number of items in this document.
     *
     * @return int
     */
    public function count()
    {
        return count($this->attributes);
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * Compare between collection or array
     *
     * @param mixed $another
     * @return int
     */
    public function compare($another)
    {
        if ($another instanceof Collection) {
            $another = $another->toArray();
        }

        $me = $this->toArray();

        if ($me == $another) {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * Debug info
     *
     * @return array
     */
    public function __debugInfo()
    {
        return $this->attributes;
    }

    /**
     * Serialize to json
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
