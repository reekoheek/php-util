<?php

namespace ROH\Util;

use Exception;

class Options extends Collection
{
    protected static $env = 'development';

    public static function setEnv($env)
    {
        static::$env = $env;
    }

    public static function create($attributes = [])
    {
        return new static($attributes ?: []);
    }

    public static function fromFile($path)
    {
        return (new static())->mergeFile($path);
    }

    public function merge($attributes)
    {
        $this->mergeOption($this->attributes, $attributes);
        return $this;
    }

    public function mergeFile($path)
    {
        $pathInfo = pathinfo($path);

        $envPath = $pathInfo['dirname'].'/'.$pathInfo['filename'].'-'.static::$env.'.'.$pathInfo['extension'];

        if (is_readable($path)) {
            $attributes = $this->requireFile($path);
            $this->merge($attributes);
        }

        if (is_readable($envPath)) {
            $envAttributes = $this->requireFile($envPath);
            $this->merge($envAttributes);
        }

        return $this;
    }

    public function toArray()
    {
        return $this->attributes;
    }

    protected function requireFile($path)
    {
        if (!is_readable($path)) {
            throw new Exception('Unreadable config file at '.$path);
        }

        return require($path);
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
                    $to[$key] = [];
                }
                $this->mergeOption($to[$key], $from[$key]);
            } else {
                $to[$key] = $from[$key];
            }
        }
    }
}
