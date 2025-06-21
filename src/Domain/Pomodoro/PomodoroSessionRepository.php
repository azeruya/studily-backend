<?php

namespace App\Domain\Pomodoro;

interface PomodoroSessionRepository
{
    public function logSession(int $userId, int $studyLogId, string $startTime, string $endTime, bool $isCompleted): bool;
    public function getSessionsByUser(int $userId): array;
}
