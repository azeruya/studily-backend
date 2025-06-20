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
}
