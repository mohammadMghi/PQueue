<?php

class DB
{
    public function __construct(private $pdo) {}

    public function count()
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM jobs');
        return $stmt->fetchColumn();
    }

    public function get($id) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM jobs WHERE id = ?"
        );

        $stmt->execute([$id]);

        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($job) {
            return $job;
        }

        return null;
    }
}