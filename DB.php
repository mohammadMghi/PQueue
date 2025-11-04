<?php

class DB
{
    public function __construct(private $pdo) {}

    public function count()
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM jobs');
        return $stmt->fetchColumn();
    }
}