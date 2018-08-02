<?php
namespace ROH\Util;

use Exception;

class Composition
{
    protected $attributes = [];

    protected $core;

    protected $stack;

    protected $context;

    /**
     * Add callback to composition
     *
     * @param callable $callback
     * @return Composition
     */
    public function compose(callable $callback)
    {

        $this->attributes[] = $callback;

        return $this;
    }

    /**
     * Check whether composition is compiled
     *
     * @return boolean
     */
    public function isCompiled()
    {
        return null !== $this->stack;
    }

    /**
     * Compile composition
     *
     * @return Composition
     */
    public function compile()
    {
        if (!$this->isCompiled()) {
            $this->stack = [$this];
            $len = count($this->attributes);
            for ($i = $len - 1; $i >= 0; $i--) {
                $next = $this->stack[0];
                $handler = $this->attributes[$i];
                array_unshift($this->stack, function () use ($next, $handler) {
                    call_user_func($handler, $this->context, $next);
                    return $this->context;
                });
            }
        }

        return $this;
    }

    /**
     * Set core callable
     *
     * @param callable $core
     * @return Composition
     */
    public function setCore(callable $core)
    {
        $this->core = $core;
        return $this;
    }

    /**
     * Apply/run composition
     *
     * @param mixed $context
     * @return mixed
     */
    public function apply($context = null)
    {
        if (null === $this->core) {
            throw new Exception('Core is undefined');
        }

        $this->context = $context;
        return $this->compile()->stack[0]($context);
    }

    public function __invoke()
    {
        $core = $this->core;
        return $core($this->context);
    }

    public function __debugInfo()
    {
        return [
            'chain' => $this->attributes,
        ];
    }
}
