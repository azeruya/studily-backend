<?php

namespace App\Domain\Study;

interface StudyLogRepository
{
    public function logStudy(int $userId): bool;
    public function hasLoggedToday(int $userId): bool;
    public function getCurrentStreak(int $userId): int;
    public function updateStreak(int $userId): void;
    public function unlockCharacters(int $userId, int $currentStreak): void;
    public function getUnlockedCharacters(int $userId): array;
    public function getTodayStudyLogId(int $userId): int;
    public function getPomodoroSessions(int $userId): array;
}