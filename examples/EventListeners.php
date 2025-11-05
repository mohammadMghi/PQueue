<?php

/**
 * Example: Setting up event listeners
 * This demonstrates the Observer pattern implementation
 */

$app = require __DIR__ . '/../bootstrap.php';

use PQueue\Contracts\EventDispatcherInterface;

$events = $app->events();

// Listen to job processing events
$events->listen('job.processing', function(array $payload) {
    echo "Job #{$payload['job_id']} is starting to process...\n";
});

$events->listen('job.processed', function(array $payload) {
    echo "Job #{$payload['job_id']} completed successfully!\n";
});

$events->listen('job.failed', function(array $payload) {
    echo "Job #{$payload['job_id']} failed (attempt {$payload['attempts']}): {$payload['error']}\n";
});

$events->listen('job.failed_permanently', function(array $payload) {
    echo "Job #{$payload['job_id']} has permanently failed after max attempts.\n";
});

// Now when you run the worker, these events will be triggered
echo "Event listeners registered!\n";
echo "Run the worker to see events in action.\n";

