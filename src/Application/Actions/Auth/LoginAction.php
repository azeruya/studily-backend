<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Actions\Action;
use App\Application\Settings\SettingsInterface;
use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use PDO;

class LoginAction extends Action
{
    private SettingsInterface $settings;
    private PDO $pdo;

    public function __construct(LoggerInterface $logger, SettingsInterface $settings, PDO $pdo)
    {
        parent::__construct($logger);
        $this->settings = $settings;
        $this->pdo = $pdo;
    }

    protected function action(): Response
    {
        $data = $this->request->getParsedBody();

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            return $this->respondWithData(['error' => 'Email and password are required'], 400);
        }

        //Fetch user
        $stmt = $this->pdo->prepare("SELECT id, password FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->respondWithData(['error' => 'Invalid credentials'], 401);
        }

        // Generate JWT
        $payload = [
            'sub' => $user['id'],
            'email' => $email,
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24), // expires in 1 day
        ];

        $jwtSecret = $_ENV['JWT_SECRET'] ?? 'changeme';
        $token = JWT::encode($payload, $jwtSecret, 'HS256');

        return $this->respondWithData(['token' => $token]);
    }
}
