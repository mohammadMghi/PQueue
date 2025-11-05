<?php

namespace PQueue\Jobs;

use PQueue\Contracts\JobInterface;
use PQueue\Exceptions\JobNotFoundException;

/**
 * Job Factory - Factory Pattern
 * Creates job instances from class names
 */
class JobFactory {
    /**
     * Create a job instance from class name and arguments
     */
    public static function create(string $className, array $arguments = []): JobInterface {
        if (!class_exists($className)) {
            throw new JobNotFoundException("Job class '{$className}' not found");
        }

        $job = new $className(...$arguments);

        if (!$job instanceof JobInterface) {
            throw new \InvalidArgumentException("Class '{$className}' must implement JobInterface");
        }

        return $job;
    }

    /**
     * Create job from serialized data
     */
    public static function fromPayload(string $payload): JobInterface {
        $job = unserialize(base64_decode($payload));
        
        if (!$job instanceof JobInterface) {
            throw new \InvalidArgumentException("Deserialized object is not a valid JobInterface instance");
        }

        return $job;
    }
}

