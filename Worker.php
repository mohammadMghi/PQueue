<?php

/**
 * Worker Entry Point
 * Uses the new architecture with design patterns
 */

$app = require __DIR__ . '/bootstrap.php';

use PQueue\Console\CommandParser;
use PQueue\Support\Config;

$args = CommandParser::parse($argv);
$config = Config::getInstance();

$tries = (int) ($args['tries'] ?? $config->get('queue.max_attempts', 3));
$id = $args['id'] ?? null;
$runEvery = (int) ($args['run-every'] ?? 1);
$queueName = $args['queue-name'] ?? $config->get('queue.default_queue', 'default');
$stopWhenEmpty = $args['stop-when-empty'] ?? false;

$worker = $app->worker();

// Process specific job by ID
if ($id) {
    $success = $worker->processJobById((int)$id, $tries);
    exit($success ? 0 : 1);
}

// Start worker loop
$worker->start([
    'queue-name' => $queueName,
    'run-every' => $runEvery,
    'stop-when-empty' => $stopWhenEmpty,
]);
