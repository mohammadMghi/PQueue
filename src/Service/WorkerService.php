<?php

namespace PQueue\Service;

use PQueue\Contracts\QueueInterface;
use PQueue\Contracts\RepositoryInterface;
use PQueue\Support\Logger;

/**
 * Worker Service - Service Layer Pattern
 * Manages the worker loop and job processing
 */
class WorkerService {
    public function __construct(
        private QueueInterface $queue,
        private RepositoryInterface $repository,
        private JobProcessor $processor,
        private Logger $logger
    ) {}

    /**
     * Start the worker loop
     */
    public function start(array $options): void {
        $queueName = $options['queue-name'] ?? 'default';
        $runEvery = (int) ($options['run-every'] ?? 1);
        $stopWhenEmpty = $options['stop-when-empty'] ?? false;

        $this->logger->info("Worker started for queue: {$queueName}");

        while (true) {
            if ($stopWhenEmpty && $this->repository->count() == 0) {
                $this->logger->info("No jobs remaining, stopping worker");
                break;
            }

            $jobData = $this->queue->pop($queueName);

            if ($jobData) {
                $this->processor->process($jobData);
            } else {
                sleep($runEvery);
            }
        }
    }

    /**
     * Process a specific job by ID
     */
    public function processJobById(int $id, int $tries = 3): bool {
        $jobData = $this->repository->get($id);

        if (!$jobData) {
            $this->logger->error("Job #{$id} not found!");
            return false;
        }

        $attempt = 0;
        $success = false;

        while ($attempt < $tries && !$success) {
            try {
                $success = $this->processor->process($jobData);
                if ($success) {
                    break;
                }
            } catch (\Exception $e) {
                $attempt++;
                $this->logger->warning("Attempt {$attempt}/{$tries} failed for job #{$id}: {$e->getMessage()}");
                
                if ($attempt < $tries) {
                    sleep(1);
                }
            }
        }

        return $success;
    }
}

