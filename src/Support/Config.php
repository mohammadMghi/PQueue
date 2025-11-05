<?php

namespace PQueue\Support;

/**
 * Configuration Service - Singleton Pattern
 */
class Config {
    private static ?Config $instance = null;
    private array $config = [];

    private function __construct() {
        $this->load();
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function load(): void {
        $configFile = __DIR__ . '/../../config.php';
        if (file_exists($configFile)) {
            $loadedConfig = require $configFile;
            $this->config = is_array($loadedConfig) ? $loadedConfig : $this->getDefaults();
        } else {
            $this->config = $this->getDefaults();
        }
    }

    public function get(string $key, mixed $default = null): mixed {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public function set(string $key, mixed $value): void {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $k) {
            if (!isset($config[$k]) || !is_array($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config = $value;
    }

    private function getDefaults(): array {
        return [
            'database' => [
                'host' => getenv('DB_HOST') ?: 'localhost',
                'dbname' => getenv('DB_NAME') ?: 'test',
                'username' => getenv('DB_USER') ?: 'root',
                'password' => getenv('DB_PASSWORD') ?: '852456',
            ],
            'queue' => [
                'default_queue' => 'default',
                'max_attempts' => 3,
                'retry_delay' => 1,
            ],
        ];
    }
}

