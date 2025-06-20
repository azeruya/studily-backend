<?php

namespace App\Infrastructure\Persistence\Character;

use App\Domain\Character\CharacterRepository;
use PDO;

class PostgresCharacterRepository implements CharacterRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllCharacters(): array
    {
        $stmt = $this->pdo->query('SELECT id, name, image_url, streak_required FROM characters ORDER BY streak_required ASC');
        return $stmt->fetchAll();
    }

    // Other methods...
}
