<?php

namespace ROH\Util;

class Thing extends Collection
{
    protected $handler;

    public function __construct($attributes)
    {
        if (is_callable($attributes) || is_object($attributes)) {
            $this->handler = $attributes;
        } else {
            parent::__construct($attributes);
        }

    }

    public function getHandler()
    {
        if (is_null($this->handler) && isset($this->attributes['class'])) {
            if (isset($this->attributes['config'])) {
                $config = $this->attributes['config'];
                if ($config instanceof \Closure) {
                    $config = $config();
                }
                $this->handler = new $this->attributes['class']($config);
            } else {
                $this->handler = new $this->attributes['class']();
            }
        }
        return $this->handler;
    }
}
