<?php

/**
 * Enqueue Entry Point
 * Uses the new architecture with design patterns
 */

$app = require __DIR__ . '/bootstrap.php';

use PQueue\Jobs\SendEmailJob;

$queue = $app->queue();

// Example: Enqueue a job
$job = new SendEmailJob("user@example.com", "Welcome!");
$queue->push($job, 'default', 1);

echo "Job enqueued successfully!\n";
