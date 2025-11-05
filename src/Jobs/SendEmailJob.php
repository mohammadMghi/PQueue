<?php

namespace PQueue\Jobs;

/**
 * Send Email Job - Concrete Command
 */
class SendEmailJob extends BaseJob {
    public function __construct(
        private string $to,
        private string $message,
        private string $subject = "Subject"
    ) {}

    protected function execute(): void {
        $result = mail($this->to, $this->subject, $this->message);
        if (!$result) {
            throw new \Exception("Failed to send email to {$this->to}");
        }
    }
}

