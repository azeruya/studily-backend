<?php

namespace App\Domain\Task;

interface TaskRepository
{
    public function createTask(array $data): int;
    public function findTasksByUserId(int $userId): array;
    public function updateTask(int $taskId, array $data): bool;
    public function deleteTask(int $taskId): bool;
}
