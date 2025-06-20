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

        // Validate input
        if (!isset($data['user_id'], $data['title'])) {
            return $this->respondWithData(['error' => 'user_id and title are required'], 400);
        }

        $taskId = $this->taskRepository->createTask([
            'user_id' => $data['user_id'],
            'title' => $data['title'],
        ]);

        return $this->respondWithData(['message' => 'Task created', 'task_id' => $taskId], 201);
    }
}
