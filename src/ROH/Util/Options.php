<?php

namespace ROH\Util;

use F\App;

class Options extends Collection
{
    protected $env;

    public static function create($attributes, $env = null)
    {
        return new static($attributes, $env);
    }

    public function __construct($attributes = [], $env = null)
    {
        if (is_null($env)) {
            $env = App::getInstance()->getOption('env');
        }

        $this->env = $env;
        parent::__construct($attributes);
    }

    public function merge($attributes)
    {
        $this->mergeOption($this->attributes, $attributes);
        return $this;
    }

    public function mergeFile($path)
    {
        $pathInfo = pathinfo($path);

        $envPath = $pathInfo['dirname'].'/'.$pathInfo['filename'].'-'.$this->env.'.'.$pathInfo['extension'];

        $attributes = require($path);
        $this->merge($attributes);

        if (is_readable($envPath)) {
            $envAttributes = require($envPath);
            $this->merge($envAttributes);
        }

        return $this;
    }

    public function toArray()
    {
        return $this->attributes;
    }

    protected function mergeOption(&$to, $from)
    {
        foreach ($from as $i => $value) {
            $f = explode('!', $i);
            $key = $f[0];
            $action = count($f) === 1 ? 'merge' : $f[1];

            if ($action === 'unset') {
                unset($to[$key]);
            } elseif ($action === 'set') {
                $to[$key] = $from[$i];
            } elseif (is_array($from[$key])) {
                if (!isset($to[$key]) || !is_array($to[$key])) {
                    $to[$key] = array();
                }
                $this->mergeOption($to[$key], $from[$key]);
            } else {
                $to[$key] = $from[$key];
            }
        }
    }
}
