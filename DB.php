<?php

class DB
{
    public function __construct(private PDO $pdo) {}

    public function count(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM jobs WHERE status != "success"');
        return (int) $stmt->fetchColumn();
    }

    public function get(int $id): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM jobs WHERE id = ?"
        );

        $stmt->execute([$id]);

        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        return $job ?: null;
    }
}