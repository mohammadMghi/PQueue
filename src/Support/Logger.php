<?php

namespace PQueue\Support;

/**
 * Logger - Singleton Pattern (improved)
 */
class Logger {
    private static ?Logger $instance = null;
    private string $logFile;
    private array $levels = ['DEBUG', 'INFO', 'WARNING', 'ERROR'];

    private function __construct(string $logFile = 'queue.log') {
        $this->logFile = $logFile;
    }

    public static function getInstance(string $logFile = 'queue.log'): self {
        if (self::$instance === null) {
            self::$instance = new self($logFile);
        }
        return self::$instance;
    }

    public function info(string $message): void {
        $this->log('INFO', $message);
    }

    public function error(string $message): void {
        $this->log('ERROR', $message);
    }

    public function warning(string $message): void {
        $this->log('WARNING', $message);
    }

    public function debug(string $message): void {
        $this->log('DEBUG', $message);
    }

    private function log(string $level, string $message): void {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
        echo $logMessage;
    }
}

