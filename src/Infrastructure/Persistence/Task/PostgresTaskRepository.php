<?php

namespace App\Infrastructure\Persistence\Task;

use App\Domain\Task\TaskRepository;
use PDO;

class PostgresTaskRepository implements TaskRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createTask(array $data): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO tasks (user_id, title) VALUES (:user_id, :title) RETURNING id");
        $stmt->execute($data);
        return $stmt->fetchColumn();
    }

    public function findTasksByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tasks WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function updateTask(int $taskId, array $data): bool
    {
        $stmt = $this->pdo->prepare("UPDATE tasks SET title = :title, is_completed = :is_completed, completed_at = :completed_at WHERE id = :id");
        return $stmt->execute([
            'title' => $data['title'],
            'is_completed' => $data['is_completed'],
            'completed_at' => $data['completed_at'],
            'id' => $taskId
        ]);
    }

    public function deleteTask(int $taskId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM tasks WHERE id = :id");
        return $stmt->execute(['id' => $taskId]);
    }
}
