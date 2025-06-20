<?php

namespace App\Domain\Study;

interface StudyLogRepository
{
    public function logStudy(int $userId): bool;
    public function hasLoggedToday(int $userId): bool;
    public function getCurrentStreak(int $userId): int;
    public function updateStreak(int $userId): void;
}