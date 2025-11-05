<?php

namespace PQueue\Support;

use Closure;
use ReflectionClass;
use ReflectionParameter;

/**
 * Dependency Injection Container - Service Locator Pattern
 * Manages dependencies and provides dependency injection
 */
class Container {
    private array $bindings = [];
    private array $instances = [];
    private array $singletons = [];

    /**
     * Bind a class or interface to a concrete implementation
     */
    public function bind(string $abstract, $concrete = null, bool $singleton = false): void {
        if ($concrete === null) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = $concrete;
        
        if ($singleton) {
            $this->singletons[$abstract] = true;
        }
    }

    /**
     * Bind a singleton instance
     */
    public function singleton(string $abstract, $concrete = null): void {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Resolve a class from the container
     */
    public function make(string $abstract): mixed {
        // Return singleton instance if already resolved
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $concrete = $this->bindings[$abstract] ?? $abstract;

        // If it's a closure, execute it
        if ($concrete instanceof Closure) {
            $instance = $concrete($this);
        } else {
            $instance = $this->build($concrete);
        }

        // Store singleton instance
        if (isset($this->singletons[$abstract])) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * Build a concrete instance with dependency injection
     */
    private function build(string $concrete): object {
        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new \Exception("Class {$concrete} is not instantiable");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $concrete();
        }

        $parameters = $constructor->getParameters();
        $dependencies = $this->resolveDependencies($parameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Resolve constructor dependencies
     */
    private function resolveDependencies(array $parameters): array {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $this->resolveParameter($parameter);
            $dependencies[] = $dependency;
        }

        return $dependencies;
    }

    /**
     * Resolve a single parameter
     */
    private function resolveParameter(ReflectionParameter $parameter): mixed {
        $type = $parameter->getType();

        if ($type === null || $type->isBuiltin()) {
            // Try to get from bindings or return null
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }
            throw new \Exception("Cannot resolve parameter {$parameter->getName()}");
        }

        $typeName = $type->getName();
        
        // Check if it's bound in container
        if (isset($this->bindings[$typeName])) {
            return $this->make($typeName);
        }

        // Try to build it
        return $this->build($typeName);
    }

    /**
     * Check if a binding exists
     */
    public function bound(string $abstract): bool {
        return isset($this->bindings[$abstract]);
    }
}

