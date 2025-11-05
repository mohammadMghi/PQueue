<?php

namespace PQueue\Contracts;

/**
 * Job Interface - Command Pattern
 * All jobs must implement this interface
 */
interface JobInterface {
    public function handle(): void;
}

