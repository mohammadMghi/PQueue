<?php

namespace PQueue\Contracts;

/**
 * Event Dispatcher Interface - Observer Pattern
 */
interface EventDispatcherInterface {
    public function dispatch(string $event, array $payload = []): void;
    public function listen(string $event, callable $listener): void;
}

