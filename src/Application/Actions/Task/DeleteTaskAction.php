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

        $success = $this->taskRepository->deleteTask($taskId);

        if ($success) {
            return $this->respondWithData(['message' => 'Task deleted']);
        }

        return $this->respondWithData(['error' => 'Task not found or could not be deleted'], 404);
    }
}
