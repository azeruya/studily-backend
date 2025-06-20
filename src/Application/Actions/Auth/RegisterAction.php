<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Actions\Action;
use App\Application\Settings\SettingsInterface;
use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class RegisterAction extends Action
{
    private SettingsInterface $settings;

    public function __construct(LoggerInterface $logger, SettingsInterface $settings)
    {
        parent::__construct($logger);
        $this->settings = $settings;
    }

    protected function action(): Response
    {
        $data = $this->getFormData();

        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $password = password_hash($data['password'], PASSWORD_BCRYPT);

        $pdo = \App\Infrastructure\Persistence\Database::getConnection($this->settings);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $password]);

        return $this->respondWithData(['message' => 'User registered successfully']);
    }
}
