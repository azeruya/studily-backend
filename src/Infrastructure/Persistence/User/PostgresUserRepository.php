<?php

namespace App\Infrastructure\Persistence\User;

use App\Domain\User\User;
use App\Domain\User\UserRepository;
use PDO;

class PostgresUserRepository implements UserRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT id, name, email FROM users");
        $rows = $stmt->fetchAll();

        $users = [];
        foreach ($rows as $row) {
            $users[] = new User($row['id'], $row['name'], $row['email']);
        }

        return $users;
    }

    public function findUserById(int $id): User
    {
        $stmt = $this->pdo->prepare("SELECT id, name, email FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new \Exception("User not found");
        }

        return new User($row['id'], $row['name'], $row['email']);
    }

    public function equipCharacter(int $userId, int $characterId): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE users SET equipped_character_id = :character_id WHERE id = :user_id"
        );

        return $stmt->execute([
            'character_id' => $characterId,
            'user_id' => $userId,
        ]);
    }

    public function getEquippedCharacter(int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.name, c.image_url, c.streak_required
            FROM users u
            JOIN characters c ON u.equipped_character_id = c.id
            WHERE u.id = :user_id AND u.equipped_character_id IS NOT NULL'
        );
        $stmt->execute(['user_id' => $userId]);
        $character = $stmt->fetch();

        return $character ?: null;
    }
}
