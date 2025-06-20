<?php

declare(strict_types=1);

namespace App\Application\Actions\Study;

use App\Application\Actions\Action;
use App\Domain\Study\StudyLogRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class LogStudyAction extends Action
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
        $today = date('Y-m-d');

        if ($this->studyLogRepository->hasLoggedToday($userId)) {
            return $this->respondWithData(['message' => 'You already logged today.'], 200);
        }

        // Log today's study
        $this->studyLogRepository->logStudy($userId);

        // Fetch current streak after log
        $currentStreak = $this->studyLogRepository->getCurrentStreak($userId);

        // Update streak
        $this->studyLogRepository->updateStreak($userId, $currentStreak, $today);

        return $this->respondWithData(['message' => 'Study logged successfully.'], 201);
    }
}
