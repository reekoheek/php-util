<?php

namespace ROH\Util;

use Exception;

class Options extends Collection
{
    /**
     * [$env description]
     * @var string
     */
    protected static $env = 'development';

    /**
     * [resetEnv description]
     * @return [type] [description]
     */
    public static function resetEnv()
    {
        static::$env = 'development';
    }

    /**
     * [getEnv description]
     * @return [type] [description]
     */
    public static function getEnv()
    {
        return static::$env;
    }

    /**
     * [setEnv description]
     * @param [type] $env [description]
     */
    public static function setEnv($env)
    {
        static::$env = $env;
    }

    // public static function create($attributes = [])
    // {
    //     return new static($attributes ?: []);
    // }

    /**
     * [fromFile description]
     * @param  [type] $path [description]
     * @return [type]       [description]
     */
    // public static function fromFile($path)
    // {
    //     return (new static())->mergeFile($path);
    // }

    public function merge($attributes)
    {
        $this->mergeOption($this->attributes, $attributes);
        return $this;
    }

    public function mergeFile($path)
    {
        $pathInfo = pathinfo($path);

        $envPath = $pathInfo['dirname'].'/'.$pathInfo['filename'].'-'.static::$env.(isset($pathInfo['extension']) ? '.'.$pathInfo['extension'] : '');

        $this->merge($this->requireFile($path));
        $this->merge($this->requireFile($envPath));

        return $this;
    }

    public function toArray()
    {
        return $this->attributes;
    }

    protected function requireFile($path)
    {
        return is_readable($path) ? require($path) : [];
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
