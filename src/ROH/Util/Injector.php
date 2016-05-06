<?php

namespace ROH\Util;

use ReflectionClass;
use Exception;

class Injector
{
    protected static $instance;

    protected $singletons = [];

    protected $delegators = [];

    protected $aliases = [];

    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function __construct()
    {
        $this->singleton(static::class, $this);
    }

    public function resolve($contract, array $args = [])
    {
        // return contract if contract is signature of function
        if (is_callable($contract)) {
            return $contract;
        } elseif (is_string($contract)) {
            return $this->resolveClass($contract, $args);
        } elseif (is_array($contract) && 2 === count($contract) && isset($contract[1])) {
            $realContract = $contract[0];
            $realArgs = (new Options(isset($contract[1]) ? $contract[1] : []))
                ->merge($args)
                ->toArray();

            return $this->resolveClass($realContract, $realArgs);
        } else {
            return $contract;
        }
    }

    protected function resolveClass($contract, array $args = [])
    {
        $contract = $this->resolveAlias($contract);

        if (isset($this->singletons[$contract])) {
            return $this->singletons[$contract];
        } elseif (isset($this->delegators[$contract])) {
            $delegator = $this->delegators[$contract];
            return $delegator($args);
        }

        $refClass = new ReflectionClass($contract);
        if (!$refClass->isInstantiable()) {
            throw new InjectorException('Injector cannot resolve contract, ' . $contract. ' is not instantiable');
        }

        $argAsParams = [];

        $constructor = $refClass->getConstructor();
        if (null !== $constructor) {
            $parameters = $constructor->getParameters();
            foreach ($parameters as $index => $parameter) {
                $name = $parameter->getName();
                $atName = '@'.$name;
                $class = $parameter->getClass();
                $isOptional = $parameter->isOptional();
                $argAsParamSet = false;
                $lastError = null;

                if (isset($args[$name]) || array_key_exists($name, $args)) {
                    $argAsParam = $args[$name];
                    $argAsParamSet = true;
                } elseif (isset($args[$atName]) && ($className = $args[$atName])) {
                    $argAsParam = $this->resolveClass($className);
                    $argAsParamSet = true;
                } elseif (isset($class)) {
                    $argContract = $this->resolveAlias($class->getName());
                    if (isset($this->singletons[$argContract])) {
                        $argAsParam = $this->singletons[$argContract];
                        $argAsParamSet = true;
                    } elseif (isset($this->delegators[$argContract])) {
                        $delegator = $this->delegators[$argContract];
                        $argAsParam = $delegator($args);
                        $argAsParamSet = true;
                    } elseif ($isOptional) {
                        break;
                    } else {
                        try {
                            $argAsParam = $this->resolveClass($argContract);
                            $argAsParamSet = true;
                        } catch (InjectorException $e) {
                            $lastError = $e;
                        }
                    }
                } elseif ($isOptional) {
                    break;
                }

                if (!$argAsParamSet) {
                    throw $lastError ?: new InjectorException('Unresolved parameter #' . $index . ' ($'.$name.') of ' . $contract);
                } else {
                    $argAsParams[] = $argAsParam;
                }
            }
        }

        return $refClass->newInstanceArgs($argAsParams);
    }

    protected function resolveAlias($contract)
    {
        if (isset($this->aliases[$contract])) {
            return $this->resolveAlias($this->aliases[$contract]);
        } else {
            return $contract;
        }
    }

    public function singleton($contract, $value)
    {
        $this->singletons[$contract] = $value;
        return $this;
    }

    public function delegate($contract, $delegator)
    {
        $this->delegators[$contract] = $delegator;
        return $this;
    }

    public function alias($contract, $alias)
    {
        $this->aliases[$contract] = $alias;
        return $this;
    }

    public function __debugInfo()
    {
        return [
            'singletons' => array_keys($this->singletons),
            'delegators' => array_keys($this->delegators),
            'aliases' => $this->aliases,
        ];
    }
}