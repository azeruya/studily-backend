<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Study;

use App\Domain\Study\StudyLogRepository;
use PDO;
use DateTime;

class PostgresStudyLogRepository implements StudyLogRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function hasLoggedToday(int $userId): bool
    {
        $today = (new DateTime())->format('Y-m-d');
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM user_study_logs WHERE user_id = :user_id AND study_date = :today");
        $stmt->execute([
            'user_id' => $userId,
            'today' => $today,
        ]);
        return (bool) $stmt->fetchColumn();
    }

    public function logStudy(int $userId): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO user_study_logs (user_id, study_date) VALUES (:user_id, :study_date)');
        return $stmt->execute([
            'user_id' => $userId,
            'study_date' => date('Y-m-d')
        ]);
    }

    public function updateStreak(int $userId): void
    {
        $today = new DateTime();
        $yesterday = (clone $today)->modify('-1 day')->format('Y-m-d');
        $todayFormatted = $today->format('Y-m-d');

        // Check if the user studied yesterday
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM user_study_logs WHERE user_id = :user_id AND study_date = :yesterday");
        $stmt->execute([
            'user_id' => $userId,
            'yesterday' => $yesterday,
        ]);

        $studiedYesterday = (bool) $stmt->fetchColumn();

        if ($studiedYesterday) {
            // Increase streak
            $this->pdo->prepare("UPDATE users SET current_streak = current_streak + 1, last_study_date = :today WHERE id = :user_id")
                ->execute([
                    'user_id' => $userId,
                    'today' => $todayFormatted,
                ]);
        } else {
            // Reset streak
            $this->pdo->prepare("UPDATE users SET current_streak = 1, last_study_date = :today WHERE id = :user_id")
                ->execute([
                    'user_id' => $userId,
                    'today' => $todayFormatted,
                ]);
        }
    }

    public function getCurrentStreak(int $userId): int
    {
        $stmt = $this->pdo->prepare("SELECT current_streak FROM users WHERE id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }
}
