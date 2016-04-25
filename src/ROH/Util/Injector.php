<?php

namespace ROH\Util;

use Exception;
use ReflectionClass;

class Injector
{
    protected $singletons = [];

    protected $delegators = [];

    public function resolve($contract, array $args = [])
    {

        // return contract if contract is signature of function
        if (is_callable($contract)) {
            return $contract;
        } elseif (is_array($contract)) {
            $realContract = $contract[0];
            $realArgs = Options::create(isset($contract[1]) ? $contract[1] : [])
                ->merge($args)
                ->toArray();

            return $this->resolve($realContract, $realArgs);
        } elseif (is_string($contract)) {
            if (isset($this->singletons[$contract])) {
                return $this->singletons[$contract];
            } elseif (isset($this->delegators[$contract])) {
                $delegator = $this->delegators[$contract];
                return $delegator($args);
            } else {
                return $this->resolveClass($contract, $args);
            }
        } else {
            return $contract;
        }
    }

    protected function resolveClass($contract, array $args = [])
    {
        $refClass = new ReflectionClass($contract);
        if (!$refClass->isInstantiable()) {
            throw new Exception('Injector cannot resolve contract, ' . $contract. ' is not instantiable');
        }

        $argAsParams = [];

        $constructor = $refClass->getConstructor();
        if (null !== $constructor) {
            $parameters = $constructor->getParameters();
            foreach ($parameters as $index => $parameter) {
                $name = $parameter->getName();
                $class = $parameter->getClass();
                $isOptional = $parameter->isOptional();
                if (isset($args[$name])) {
                    $argAsParams[] = $args[$name];
                } elseif (isset($args['@'.$name]) && ($className = $args['@'.$name])) {
                    $argAsParams[] = $this->resolve($className);
                } elseif ($isOptional) {
                    break;
                } elseif (isset($class)) {
                    $argAsParams[] = $this->resolve($class->getName());
                } else {
                    throw new Exception('Unresolved parameter #' . $index . ' ($'.$name.') of ' . $contract);
                }
            }
        }

        return $refClass->newInstanceArgs($argAsParams);
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

    public function __debugInfo()
    {
        return [
            'singletons' => array_keys($this->singletons),
            'delegators' => array_keys($this->delegators),
        ];
    }
}