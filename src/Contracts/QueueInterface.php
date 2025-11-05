<?php

namespace PQueue\Contracts;

/**
 * Queue Interface - Strategy Pattern
 * Allows different queue implementations (Database, Redis, etc.)
 */
interface QueueInterface {
    public function push(JobInterface $job, string $queue = 'default', int $priority = 0): void;
    public function pop(string $queue = 'default'): ?array;
    public function delete(int $id): void;
    public function markJobAsFailed(int $id): void;
    public function incrementAttempts(int $id): void;
    public function getAttempts(int $id): int;
    public function release(int $id): void;
}

