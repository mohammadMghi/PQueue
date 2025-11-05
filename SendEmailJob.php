<?php
require 'JobInterface.php';

class SendEmailJob implements JobInterface {
    public string $to;
    public string $message;

    public function __construct(string $to, string $message) {
        $this->to = $to;
        $this->message = $message;
    }

    public function handle(): void {
        // Example implementation - replace with actual email sending logic
        $result = mail($this->to, "Subject", $this->message);
        if (!$result) {
            throw new Exception("Failed to send email to {$this->to}");
        }
    } 
}
