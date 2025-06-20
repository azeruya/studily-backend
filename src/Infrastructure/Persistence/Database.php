<?php

namespace App\Infrastructure\Persistence;

use PDO;
use App\Application\Settings\SettingsInterface;

class Database
{
    public static function getConnection(SettingsInterface $settings): PDO
    {
        $db = $settings->get('db');

        $dsn = "{$db['driver']}:host={$db['host']};port={$db['port']};dbname={$db['database']}";
        return new PDO($dsn, $db['username'], $db['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}
