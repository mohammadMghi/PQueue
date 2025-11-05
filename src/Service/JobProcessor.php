<?php

namespace PQueue\Service;

use PQueue\Contracts\EventDispatcherInterface;
use PQueue\Contracts\QueueInterface;
use PQueue\Jobs\JobFactory;
use PQueue\Jobs\BaseJob;
use PQueue\Support\Logger;
use PQueue\Support\Config;

/**
 * Job Processor Service - Service Layer Pattern
 * Handles job execution logic
 */
class JobProcessor {
    public function __construct(
        private QueueInterface $queue,
        private EventDispatcherInterface $events,
        private Logger $logger,
        private Config $config
    ) {}

    /**
     * Process a single job
     */
    public function process(array $jobData): bool {
        $jobId = (int)$jobData['id'];
        
        try {
            $this->events->dispatch('job.processing', ['job_id' => $jobId, 'job_data' => $jobData]);
            
            $job = JobFactory::fromPayload($jobData['payload']);
            $this->executeJob($job, $jobId);
            
            $this->queue->delete($jobId);
            $this->events->dispatch('job.processed', ['job_id' => $jobId]);
            $this->logger->info("Successfully processed job #{$jobId}");
            
            return true;
        } catch (\Exception $e) {
            return $this->handleJobFailure($jobId, $e, $jobData);
        }
    }

    /**
     * Execute a job with retry logic
     */
    private function executeJob($job, int $jobId): void {
        if ($job instanceof BaseJob) {
            $timeout = $job->getTimeout();
            // In a real implementation, you might want to set a timeout here
        }

        $job->handle();
    }

    /**
     * Handle job failure with retry logic
     */
    private function handleJobFailure(int $jobId, \Exception $e, array $jobData): bool {
        $this->queue->incrementAttempts($jobId);
        $attempts = $this->queue->getAttempts($jobId);
        $maxAttempts = $this->config->get('queue.max_attempts', 3);

        $this->events->dispatch('job.failed', [
            'job_id' => $jobId,
            'attempts' => $attempts,
            'error' => $e->getMessage()
        ]);

        if ($attempts >= $maxAttempts) {
            $this->queue->markJobAsFailed($jobId);
            $this->events->dispatch('job.failed_permanently', ['job_id' => $jobId]);
            $this->logger->error("Job #{$jobId} failed permanently after {$attempts} attempts: {$e->getMessage()}");
            return false;
        }

        // Release job back to queue for retry
        $this->queue->release($jobId);
        $this->logger->warning("Job #{$jobId} failed (attempt {$attempts}/{$maxAttempts}): {$e->getMessage()}");
        return false;
    }
}

