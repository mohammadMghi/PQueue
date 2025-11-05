<?php

class Logger {
    private static $instance = null;
    private $logFile;

    private function __construct($logFile = 'queue.log') {
        $this->logFile = $logFile;
    }

    public static function getInstance($logFile = 'queue.log') {
        if (self::$instance === null) {
            self::$instance = new self($logFile);
        }
        return self::$instance;
    }

    public function info($message) {
        $this->log('INFO', $message);
    }

    public function error($message) {
        $this->log('ERROR', $message);
    }

    public function warning($message) {
        $this->log('WARNING', $message);
    }

    private function log($level, $message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
        echo $logMessage; // Also output to console
    }
}

