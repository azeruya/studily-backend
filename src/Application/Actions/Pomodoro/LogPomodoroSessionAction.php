<?php

declare(strict_types=1);

namespace App\Application\Actions\Pomodoro;

use App\Application\Actions\Action;
use App\Domain\Pomodoro\PomodoroSessionRepository;
use App\Domain\Study\StudyLogRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class LogPomodoroSessionAction extends Action
{
    private PomodoroSessionRepository $pomodoroRepository;
    private StudyLogRepository $studyLogRepository;

    public function __construct(
        LoggerInterface $logger,
        PomodoroSessionRepository $pomodoroRepository,
        StudyLogRepository $studyLogRepository
    ) {
        parent::__construct($logger);
        $this->pomodoroRepository = $pomodoroRepository;
        $this->studyLogRepository = $studyLogRepository;
    }

    protected function action(): Response
    {
        $userId = $this->request->getAttribute('token')->sub;
        $data = $this->request->getParsedBody();

        $startTime = $data['start_time'] ?? null;
        $endTime = $data['end_time'] ?? null;
        $isCompleted = $data['is_completed'] ?? true;

        if (!$startTime || !$endTime) {
            return $this->respondWithData(['error' => 'Missing start_time or end_time'], 400);
        }

        if (!$this->studyLogRepository->hasLoggedToday($userId)) {
            $this->studyLogRepository->logStudy($userId);
        }

        $studyLogId = $this->studyLogRepository->getTodayStudyLogId($userId);

        $this->pomodoroRepository->logSession(
            $userId,
            $studyLogId,
            $startTime,
            $endTime,
            (bool)$isCompleted
        );

        return $this->respondWithData(['message' => 'Pomodoro session logged'], 201);
    }
}
