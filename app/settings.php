<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => true, // Should be set to false in production
                'logError'            => false,
                'logErrorDetails'     => false,
                'logger' => [
                    'name' => 'slim-app',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                'db' => [
                    'driver'   => $_ENV['DB_DRIVER'] ?? 'pgsql',
                    'host'     => $_ENV['DB_HOST'] ?? '',
                    'port'     => $_ENV['DB_PORT'] ?? 5432,
                    'database' => $_ENV['DB_DATABASE'] ?? '',
                    'username' => $_ENV['DB_USERNAME'] ?? '',
                    'password' => $_ENV['DB_PASSWORD'] ?? '',
                ],
            ]);
        }
    ]);
};
