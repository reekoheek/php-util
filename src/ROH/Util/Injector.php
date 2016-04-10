<?php

namespace ROH\Util;

use Exception;
use ReflectionClass;

class Injector
{
    protected $singletons = [];

    protected $delegates = [];

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
            } elseif (isset($this->delegates[$contract])) {
                throw new Exception('Unimplemented yet!');
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
            throw new Exception($contract. ' is not instantiable');
        }

        $argAsParams = [];

        $constructor = $refClass->getConstructor();
        if (null !== $constructor) {
            $parameters = $constructor->getParameters();
            foreach ($parameters as $index => $parameter) {
                $name = $parameter->getName();
                $class = $parameter->getClass();
                if (isset($args[$name])) {
                    $argAsParams[] = $args[$name];
                } elseif (isset($class)) {
                    $className = isset($args['@'.$name]) ? $args['@'.$name] : $class->getName();
                    $argAsParams[] = $this->resolve($className);
                } else {
                    if ($parameter->isOptional()) {
                        break;
                    } else {
                        throw new Exception('Unresolved parameter #' . $index . ' ($'.$name.') of ' . $contract);
                    }
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

    public function __debugInfo()
    {
        return [
            'singletons' => array_keys($this->singletons),
            // 'delegates' => $this->delegates,
        ];
    }
}