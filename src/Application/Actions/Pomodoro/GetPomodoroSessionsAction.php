<?php

declare(strict_types=1);

namespace App\Application\Actions\Pomodoro;

use App\Application\Actions\Action;
use App\Domain\Study\StudyLogRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class GetPomodoroSessionsAction extends Action
{
    private StudyLogRepository $studyLogRepository;

    public function __construct(LoggerInterface $logger, StudyLogRepository $studyLogRepository)
    {
        parent::__construct($logger);
        $this->studyLogRepository = $studyLogRepository;
    }

    protected function action(): Response
    {
        $userId = $this->request->getAttribute('token')->sub;
        $sessions = $this->studyLogRepository->getPomodoroSessions($userId);

        return $this->respondWithData($sessions);
    }
}
