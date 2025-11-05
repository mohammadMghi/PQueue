<?php

namespace PQueue\Events;

use PQueue\Contracts\EventDispatcherInterface;

/**
 * Event Dispatcher - Observer Pattern
 * Manages event listeners and dispatches events
 */
class EventDispatcher implements EventDispatcherInterface {
    private array $listeners = [];

    public function listen(string $event, callable $listener): void {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }
        $this->listeners[$event][] = $listener;
    }

    public function dispatch(string $event, array $payload = []): void {
        if (!isset($this->listeners[$event])) {
            return;
        }

        foreach ($this->listeners[$event] as $listener) {
            call_user_func($listener, $payload);
        }
    }

    public function hasListeners(string $event): bool {
        return isset($this->listeners[$event]) && !empty($this->listeners[$event]);
    }
}

