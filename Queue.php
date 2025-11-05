<?php

class Queue {
    protected PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function push(JobInterface $job, string $queue = 'default', int $priority = 0): void {
        $payload = base64_encode(serialize($job));
        $stmt = $this->pdo->prepare("INSERT INTO jobs (queue, payload, priority) VALUES (?, ?, ?)");
        $stmt->execute([$queue, $payload, $priority]);
    }

    public function pop(string $queue = 'default'): ?array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM jobs
            WHERE queue = ? AND reserved_at IS NULL AND status = 'pending'
            ORDER BY priority DESC, id ASC
            LIMIT 1
        ");
        $stmt->execute([$queue]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($job) {
            $this->pdo->prepare("UPDATE jobs SET reserved_at = NOW(), status = 'processing' WHERE id = ?")
                      ->execute([$job['id']]);
            return $job;
        }

        return null;
    }

    public function failedJobs(string $queue = 'default'): ?array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM jobs WHERE status = 'failed' AND queue = ? ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$queue]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($job) {
            $this->pdo->prepare("UPDATE jobs SET reserved_at = NOW(), status = 'processing' WHERE id = ?")
                      ->execute([$job['id']]);
            return $job;
        }

        return null;
    }

    public function delete(int $id): void {
        $stmt = $this->pdo->prepare("UPDATE jobs SET status = 'success' WHERE id = ?");
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

    public function markJobAsFailed(int $id): void {
        $stmt = $this->pdo->prepare('UPDATE jobs SET status = "failed" WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function release(int $id): void {
        $stmt = $this->pdo->prepare('UPDATE jobs SET reserved_at = NULL, status = "pending" WHERE id = ?');
        $stmt->execute([$id]);
    }
}
