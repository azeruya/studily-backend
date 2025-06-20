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

        $title = $data['title'] ?? null;
        $isCompleted = $data['is_completed'] ?? false;
        $completedAt = $isCompleted ? date('Y-m-d H:i:s') : null;

        $updateData = [
            'title' => $title,
            'is_completed' => $isCompleted,
            'completed_at' => $completedAt
        ];

        $success = $this->taskRepository->updateTask($taskId, $updateData);

        if (!$success) {
            return $this->respondWithData(['error' => 'Failed to update task'], 500);
        }

        return $this->respondWithData(['message' => 'Task updated'], 200);
    }
}
