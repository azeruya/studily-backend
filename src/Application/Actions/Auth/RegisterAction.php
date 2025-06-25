<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Actions\Action;
use App\Application\Settings\SettingsInterface;
use App\Domain\Study\StudyRepository;
use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class RegisterAction extends Action
{
    private SettingsInterface $settings;
    private StudyRepository $studyRepository;

    public function __construct(
        LoggerInterface $logger,
        SettingsInterface $settings,
        StudyRepository $studyRepository
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
        $password = password_hash($data['password'], PASSWORD_BCRYPT);

        $pdo = \App\Infrastructure\Persistence\Database::getConnection($this->settings);

        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $password]);

        // Get inserted user ID
        $userId = (int) $pdo->lastInsertId();

        // Unlock starter characters
        $this->studyRepository->unlockCharacters($userId, 0);

        // Auto-equip starter character (lowest streak_required = 0)
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
    }
}
