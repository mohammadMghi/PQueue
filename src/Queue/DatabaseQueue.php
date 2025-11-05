<?php

namespace PQueue\Queue;

use PQueue\Contracts\JobInterface;
use PQueue\Contracts\QueueInterface;
use PQueue\Repository\JobRepository;

/**
 * Database Queue Implementation - Strategy Pattern
 * Concrete implementation of QueueInterface using database
 */
class DatabaseQueue implements QueueInterface {
    public function __construct(private JobRepository $repository) {}

    public function push(JobInterface $job, string $queue = 'default', int $priority = 0): void {
        $payload = base64_encode(serialize($job));
        $this->repository->create([
            'queue' => $queue,
            'payload' => $payload,
            'priority' => $priority
        ]);
    }

    public function pop(string $queue = 'default'): ?array {
        $jobs = $this->repository->findPending($queue, 1);
        
        if (empty($jobs)) {
            return null;
        }

        $job = $jobs[0];
        $this->repository->reserve((int)$job['id']);
        
        return $job;
    }

    public function delete(int $id): void {
        $this->repository->markAsSuccess($id);
    }

    public function markJobAsFailed(int $id): void {
        $this->repository->markAsFailed($id);
    }

    public function incrementAttempts(int $id): void {
        $this->repository->incrementAttempts($id);
    }

    public function getAttempts(int $id): int {
        return $this->repository->getAttempts($id);
    }

    public function release(int $id): void {
        $this->repository->release($id);
    }
}

