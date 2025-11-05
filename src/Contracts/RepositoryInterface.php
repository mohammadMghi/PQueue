<?php

namespace PQueue\Contracts;

/**
 * Repository Interface - Repository Pattern
 * Abstracts data access layer
 */
interface RepositoryInterface {
    public function count(): int;
    public function get(int $id): ?array;
    public function findById(int $id): ?array;
}

