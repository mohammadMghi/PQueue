<?php

namespace PQueue;

use PDO;
use PQueue\Contracts\EventDispatcherInterface;
use PQueue\Contracts\QueueInterface;
use PQueue\Contracts\RepositoryInterface;
use PQueue\Events\EventDispatcher;
use PQueue\Queue\DatabaseQueue;
use PQueue\Repository\JobRepository;
use PQueue\Service\JobProcessor;
use PQueue\Service\WorkerService;
use PQueue\Support\Config;
use PQueue\Support\Container;
use PQueue\Support\Logger;

/**
 * Application - Facade Pattern
 * Main application class that bootstraps and configures the system
 */
class Application {
    private Container $container;
    private Config $config;

    public function __construct() {
        $this->container = new Container();
        $this->config = Config::getInstance();
        $this->bootstrap();
    }

    /**
     * Bootstrap the application
     */
    private function bootstrap(): void {
        // Register singleton services
        $this->container->singleton(Config::class, fn() => $this->config);
        $this->container->singleton(Logger::class, fn() => Logger::getInstance());
        $this->container->singleton(EventDispatcherInterface::class, fn() => new EventDispatcher());
        
        // Register PDO
        $this->container->singleton(PDO::class, function() {
            $dbConfig = $this->config->get('database');
            $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset=utf8mb4";
            return new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        });

        // Register Repository
        $this->container->bind(RepositoryInterface::class, JobRepository::class);
        
        // Register Queue
        $this->container->bind(QueueInterface::class, function(Container $container) {
            $repository = $container->make(RepositoryInterface::class);
            return new DatabaseQueue($repository);
        });

        // Register Services
        $this->container->bind(JobProcessor::class);
        $this->container->bind(WorkerService::class);
    }

    /**
     * Get an instance from the container
     */
    public function make(string $abstract): mixed {
        return $this->container->make($abstract);
    }

    /**
     * Get the queue instance
     */
    public function queue(): QueueInterface {
        return $this->container->make(QueueInterface::class);
    }

    /**
     * Get the worker service
     */
    public function worker(): WorkerService {
        return $this->container->make(WorkerService::class);
    }

    /**
     * Get the event dispatcher
     */
    public function events(): EventDispatcherInterface {
        return $this->container->make(EventDispatcherInterface::class);
    }

    /**
     * Get the logger
     */
    public function logger(): Logger {
        return $this->container->make(Logger::class);
    }
}

