<?php

namespace PQueue\Repository;

use PDO;
use PQueue\Contracts\RepositoryInterface;

/**
 * Job Repository - Repository Pattern
 * Encapsulates database operations for jobs
 */
class JobRepository implements RepositoryInterface {
    public function __construct(private PDO $pdo) {}

    public function count(): int {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM jobs WHERE status != "success"');
        return (int) $stmt->fetchColumn();
    }

    public function get(int $id): ?array {
        return $this->findById($id);
    }

    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM jobs WHERE id = ?");
        $stmt->execute([$id]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);
        return $job ?: null;
    }

    public function findPending(string $queue = 'default', int $limit = 1): array {
        // Ensure limit is positive and reasonable
        $limit = max(1, min((int) $limit, 1000)); // Between 1 and 1000
        
        $stmt = $this->pdo->prepare("
            SELECT * FROM jobs
            WHERE queue = ? AND reserved_at IS NULL AND status = 'pending'
            ORDER BY priority DESC, id ASC
            LIMIT {$limit}
        ");
        $stmt->execute([$queue]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function reserve(int $id): void {
        $stmt = $this->pdo->prepare("
            UPDATE jobs 
            SET reserved_at = NOW(), status = 'processing' 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
    }

    public function markAsSuccess(int $id): void {
        $stmt = $this->pdo->prepare("UPDATE jobs SET status = 'success' WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function markAsFailed(int $id): void {
        $stmt = $this->pdo->prepare('UPDATE jobs SET status = "failed" WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function incrementAttempts(int $id): void {
        $stmt = $this->pdo->prepare("UPDATE jobs SET attempts = attempts + 1 WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function getAttempts(int $id): int {
        $stmt = $this->pdo->prepare("SELECT attempts FROM jobs WHERE id = ?");
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }

    public function release(int $id): void {
        $stmt = $this->pdo->prepare('UPDATE jobs SET reserved_at = NULL, status = "pending" WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function create(array $data): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO jobs (queue, payload, priority) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$data['queue'], $data['payload'], $data['priority']]);
        return (int) $this->pdo->lastInsertId();
    }
}

