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
        if ($this->hasLoggedToday($userId)) {
            return false;
        }

        $stmt = $this->pdo->prepare('INSERT INTO user_study_logs (user_id, study_date) VALUES (:user_id, :study_date)');
        $success = $stmt->execute([
            'user_id' => $userId,
            'study_date' => date('Y-m-d')
        ]);

        if ($success) {
            $this->updateStreak($userId);
            $currentStreak = $this->getCurrentStreak($userId);
            $this->unlockCharacters($userId, $currentStreak);
        }

        return $success;
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

    public function unlockCharacters(int $userId, int $currentStreak): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO user_characters (user_id, character_id)
            SELECT :user_id, c.id
            FROM characters c
            WHERE c.streak_required <= :streak
            AND NOT EXISTS (
                SELECT 1 FROM user_characters uc
                WHERE uc.user_id = :user_id AND uc.character_id = c.id
            )
        ");
        $stmt->execute([
            'user_id' => $userId,
            'streak' => $currentStreak
        ]);
    }

    public function getUnlockedCharacters(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT c.id, c.name, c.image_url, c.streak_required, uc.unlocked_at
            FROM characters c
            JOIN user_characters uc ON c.id = uc.character_id
            WHERE uc.user_id = :user_id
            ORDER BY c.streak_required
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getTodayStudyLogId(int $userId): int
    {
        $today = date('Y-m-d');
        $stmt = $this->pdo->prepare("SELECT id FROM user_study_logs WHERE user_id = :user_id AND study_date = :today");
        $stmt->execute([
            'user_id' => $userId,
            'today' => $today,
        ]);
        return (int) $stmt->fetchColumn();
    }

    public function getPomodoroSessions(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT ps.id, ps.start_time, ps.end_time, ps.is_completed, usl.study_date
            FROM pomodoro_sessions ps
            JOIN user_study_logs usl ON ps.study_log_id = usl.id
            WHERE ps.user_id = :user_id
            ORDER BY ps.start_time DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
