<?php

declare(strict_types=1);

namespace App\Application\Actions\Task;

use App\Application\Actions\Action;
use App\Domain\Task\TaskRepository;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;

class CreateTaskAction extends Action
{
    private TaskRepository $taskRepository;

    public function __construct(LoggerInterface $logger, TaskRepository $taskRepository)
    {
        parent::__construct($logger);
        $this->taskRepository = $taskRepository;
    }

    protected function action(): Response
    {
        $data = $this->request->getParsedBody();

        // Get the user ID from the JWT token
        $userId = $this->request->getAttribute('token')->sub ?? null;

        // Validate input
        if (!$userId || empty($data['title'])) {
            return $this->respondWithData(['error' => 'User not authenticated or title missing'], 400);
        }

        $taskId = $this->taskRepository->createTask([
            'user_id' => $userId,
            'title' => $data['title'],
        ]);

        return $this->respondWithData(['message' => 'Task created', 'task_id' => $taskId], 201);
    }

}
