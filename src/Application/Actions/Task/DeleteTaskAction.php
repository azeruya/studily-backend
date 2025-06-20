<?php

declare(strict_types=1);

namespace App\Application\Actions\Task;

use App\Application\Actions\Action;
use App\Domain\Task\TaskRepository;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;

class DeleteTaskAction extends Action
{
    private TaskRepository $taskRepository;

    public function __construct(LoggerInterface $logger, TaskRepository $taskRepository)
    {
        parent::__construct($logger);
        $this->taskRepository = $taskRepository;
    }

    protected function action(): Response
    {
        $taskId = (int) $this->resolveArg('taskId');
        $userId = $this->request->getAttribute('token')->sub;

        // Fetch all tasks for the user
        $tasks = $this->taskRepository->findTasksByUserId($userId);
        $task = array_filter($tasks, fn($t) => $t['id'] === $taskId);

        if (empty($task)) {
            return $this->respondWithData(['error' => 'Unauthorized or task not found'], 403);
        }

        $success = $this->taskRepository->deleteTask($taskId);

        if ($success) {
            return $this->respondWithData(['message' => 'Task deleted']);
        }

        return $this->respondWithData(['error' => 'Task could not be deleted'], 500);
    }
}
