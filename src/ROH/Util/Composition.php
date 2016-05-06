<?php
namespace ROH\Util;

use Exception;

class Composition
{
    protected $attributes = [];

    protected $core;

    protected $stack;

    public function compose(callable $callback)
    {

        $this->attributes[] = $callback;

        return $this;
    }

    public function isCompiled()
    {
        return null !== $this->stack;
    }

    public function compile()
    {
        if (!$this->isCompiled()) {
            $this->stack = [$this];
            $len = count($this->attributes);
            for ($i = $len - 1; $i >= 0; $i--) {
                $next = $this->stack[0];
                $handler = $this->attributes[$i];
                array_unshift($this->stack, function ($context) use ($next, $handler) {
                    return call_user_func($handler, $context, $next);
                });
            }
        }

        return $this;
    }

    public function setCore(callable $core)
    {
        $this->core = $core;
        return $this;
    }

    public function apply($context = null)
    {
        if (null === $this->core) {
            throw new Exception('Core is undefined');
        }
        return $this->compile()->stack[0]($context);
    }

    public function __invoke($context)
    {
        $core = $this->core;
        return $core($context);
    }

    public function __debugInfo()
    {
        return [
            'chain' => $this->attributes,
        ];
    }
}
