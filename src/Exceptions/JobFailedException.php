<?php

namespace PQueue\Exceptions;

class JobFailedException extends \Exception {
    public function __construct(string $message = "Job failed", int $code = 0, ?\Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

