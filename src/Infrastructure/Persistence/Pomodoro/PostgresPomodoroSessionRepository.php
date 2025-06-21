<?php

namespace App\Infrastructure\Persistence\Pomodoro;

use App\Domain\Pomodoro\PomodoroSessionRepository;
use App\Domain\Study\StudyLogRepository;
use PDO;

class PostgresPomodoroSessionRepository implements PomodoroSessionRepository
{
    private PDO $pdo;
    private StudyLogRepository $studyLogRepository;

    public function __construct(PDO $pdo, StudyLogRepository $studyLogRepository)
    {
        $this->pdo = $pdo;
        $this->studyLogRepository = $studyLogRepository;
    }

    public function logSession(int $userId, int $studyLogId, string $startTime, string $endTime, bool $isCompleted): bool
    {
        // 1. Log the Pomodoro session
        $stmt = $this->pdo->prepare('
            INSERT INTO pomodoro_sessions (user_id, study_log_id, start_time, end_time, is_completed)
            VALUES (:user_id, :study_log_id, :start_time, :end_time, :is_completed)
        ');
        $stmt->execute([
            'user_id' => $userId,
            'study_log_id' => $studyLogId,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_completed' => $isCompleted,
        ]);

        // Ensure study log is created and streak logic only runs once per day
        $justLogged = $this->studyLogRepository->logStudy($userId);

        // Only update streak and unlock if it was the first log today
        if ($justLogged) {
            $currentStreak = $this->studyLogRepository->getCurrentStreak($userId);
            $this->studyLogRepository->unlockCharacters($userId, $currentStreak);
        }

        return true;
    }

    public function getSessionsByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM pomodoro_sessions WHERE user_id = :user_id ORDER BY start_time DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
