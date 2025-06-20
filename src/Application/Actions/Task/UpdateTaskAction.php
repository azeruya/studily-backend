<?php

declare(strict_types=1);

namespace App\Application\Actions\Task;

use App\Application\Actions\Action;
use App\Domain\Task\TaskRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class UpdateTaskAction extends Action
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
        $data = $this->request->getParsedBody();
        $userId = $this->request->getAttribute('token')->sub;

        // Fetch task and validate ownership
        $tasks = $this->taskRepository->findTasksByUserId($userId);
        $task = array_filter($tasks, fn($t) => $t['id'] === $taskId);

        if (empty($task)) {
            return $this->respondWithData(['error' => 'Unauthorized or task not found'], 403);
        }

        // Get the existing task data (first match)
        $task = array_values($task)[0];

        $updateData = [];

        // Update title if provided
        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
        } else {
            // Keep existing title if not provided
            $updateData['title'] = $task['title'];
        }

        // Update is_completed and completed_at only if is_completed key is present
        if (array_key_exists('is_completed', $data)) {
            $isCompleted = (bool) $data['is_completed'];
            $updateData['is_completed'] = $isCompleted;
            $updateData['completed_at'] = $isCompleted ? date('Y-m-d H:i:s') : null;
        } else {
            // Keep existing completion status
            $updateData['is_completed'] = $task['is_completed'];
            $updateData['completed_at'] = $task['completed_at'];
        }

        $success = $this->taskRepository->updateTask($taskId, $updateData);

        if (!$success) {
            return $this->respondWithData(['error' => 'Failed to update task'], 500);
        }

        return $this->respondWithData(['message' => 'Task updated'], 200);
    }
}
