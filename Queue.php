<?php
class Queue {
    protected $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function push(JobInterface $job, $queue = 'default',$priority = 0) {
        $payload = base64_encode(serialize($job));
        $stmt = $this->pdo->prepare("INSERT INTO jobs (queue, payload, priority) VALUES (?, ?, ?)");
        $stmt->execute([$queue, $payload, $priority]);
    }

    public function pop($queue = 'default') {
        $stmt = $this->pdo->prepare("
            SELECT * FROM jobs
            WHERE queue = ? AND reserved_at IS NULL
            ORDER BY priority DESC, id ASC
            LIMIT 1
        ");
        $stmt->execute([$queue]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($job) {
            $this->pdo->prepare("UPDATE jobs SET reserved_at = NOW() WHERE id = ?")
                      ->execute([$job['id']]);
            return $job;
        }

        return null;
    }

    public function failedJobs($queue = 'default') {
        $stmt = $this->pdo->prepare("
            SELECT * FROM jobs WHERE status = 'failed' AND queue = ? ORDER BY created_at DESC;
        ");
        $stmt->execute([$queue]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($job) {
            $this->pdo->prepare("UPDATE jobs SET reserved_at = NOW() WHERE id = ?")
                      ->execute([$job['id']]);
            return $job;
        }

        return null;
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM jobs WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function markJobAsFailed($id) {
        $stmt = $this->pdo->prepare('UPDATE jobs WHERE id = ? SET priority = "failed"');
        $stmt->execute([$id]);
    }
}
