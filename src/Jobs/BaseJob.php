<?php

namespace PQueue\Jobs;

use PQueue\Contracts\JobInterface;

/**
 * Base Job Class - Template Method Pattern
 * Provides common functionality for all jobs
 */
abstract class BaseJob implements JobInterface {
    protected int $maxAttempts = 3;
    protected int $timeout = 60;
    protected int $retryDelay = 1;

    public function getMaxAttempts(): int {
        return $this->maxAttempts;
    }

    public function getTimeout(): int {
        return $this->timeout;
    }

    public function getRetryDelay(): int {
        return $this->retryDelay;
    }

    /**
     * Template method - defines the algorithm structure
     */
    final public function handle(): void {
        $this->before();
        $this->execute();
        $this->after();
    }

    /**
     * Hook method - called before job execution
     */
    protected function before(): void {
        // Override in subclasses if needed
    }

    /**
     * Hook method - called after job execution
     */
    protected function after(): void {
        // Override in subclasses if needed
    }

    /**
     * Abstract method - must be implemented by subclasses
     */
    abstract protected function execute(): void;
}

