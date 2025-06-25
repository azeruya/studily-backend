<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Actions\Action;
use App\Application\Settings\SettingsInterface;
use App\Domain\Study\StudyLogRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class RegisterAction extends Action
{
    private SettingsInterface $settings;
    private StudyLogRepository $studyRepository;

    public function __construct(
        LoggerInterface $logger,
        SettingsInterface $settings,
        StudyLogRepository $studyRepository
    ) {
        parent::__construct($logger);
        $this->settings = $settings;
        $this->studyRepository = $studyRepository;
    }

    protected function action(): Response
    {
        $data = $this->getFormData();

        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            return $this->respondWithData(['message' => 'Missing required fields'], 400);
        }

        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $pdo = \App\Infrastructure\Persistence\Database::getConnection($this->settings);

            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hashedPassword]);

            $userId = (int) $pdo->lastInsertId();

            // Unlock starter characters (streak_required = 0)
            $this->studyRepository->unlockCharacters($userId, 0);

            // Auto-equip the first unlocked starter character
            $equipStmt = $pdo->prepare("
                UPDATE users
                SET equipped_character_id = (
                    SELECT id FROM characters
                    WHERE streak_required = 0
                    ORDER BY id ASC
                    LIMIT 1
                )
                WHERE id = :user_id
            ");
            $equipStmt->execute(['user_id' => $userId]);

            return $this->respondWithData(['message' => 'User registered successfully']);
        } catch (\PDOException $e) {
            return $this->respondWithData([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
