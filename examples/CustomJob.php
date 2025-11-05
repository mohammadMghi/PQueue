<?php

/**
 * Example: Creating a custom job
 * This demonstrates the Template Method pattern
 */

require __DIR__ . '/../vendor/autoload.php';

use PQueue\Jobs\BaseJob;

/**
 * Custom Job Example
 * Extends BaseJob which provides the Template Method pattern
 */
class ProcessPaymentJob extends BaseJob {
    protected int $maxAttempts = 5; // Override default max attempts
    protected int $timeout = 120; // 2 minutes timeout

    public function __construct(
        private int $userId,
        private float $amount,
        private string $paymentMethod
    ) {}

    /**
     * This method is called before job execution
     */
    protected function before(): void {
        echo "Preparing to process payment for user #{$this->userId}...\n";
    }

    /**
     * Main job logic - must be implemented
     */
    protected function execute(): void {
        // Simulate payment processing
        echo "Processing payment of \${$this->amount} via {$this->paymentMethod}...\n";
        
        // Simulate potential failure
        if (rand(1, 10) > 7) {
            throw new Exception("Payment gateway temporarily unavailable");
        }
        
        // Success
        echo "Payment processed successfully!\n";
    }

    /**
     * This method is called after job execution
     */
    protected function after(): void {
        echo "Payment processing completed for user #{$this->userId}.\n";
    }
}

// Usage example:
$app = require __DIR__ . '/../bootstrap.php';
$queue = $app->queue();

$job = new ProcessPaymentJob(123, 99.99, 'credit_card');
$queue->push($job, 'payments', 10); // High priority

echo "Payment job enqueued!\n";

