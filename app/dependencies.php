<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use App\Application\Actions\Auth\RegisterAction;
use App\Application\Actions\Auth\LoginAction;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use App\Application\Actions\Task\GetTasksAction;
use App\Domain\Task\TaskRepository;
use App\Application\Actions\Task\UpdateTaskAction;
use App\Application\Actions\Task\DeleteTaskAction;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([

        // Logger binding
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);
            $loggerSettings = $settings->get('logger');

            $logger = new Logger($loggerSettings['name']);
            $logger->pushProcessor(new UidProcessor());
            $logger->pushHandler(new StreamHandler($loggerSettings['path'], $loggerSettings['level']));

            return $logger;
        },

        // PDO binding
        PDO::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);
            $db = $settings->get('db');

            $dsn = "{$db['driver']}:host={$db['host']};port={$db['port']};dbname={$db['database']}";

            return new PDO($dsn, $db['username'], $db['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        },

        // RegisterAction binding
        RegisterAction::class => function (ContainerInterface $c) {
            return new RegisterAction(
                $c->get(LoggerInterface::class),
                $c->get(SettingsInterface::class)
            );
        },

        // LoginAction binding
        LoginAction::class => function (ContainerInterface $c) {
            return new LoginAction(
                $c->get(LoggerInterface::class),
                $c->get(SettingsInterface::class),
                $c->get(PDO::class)
            );
        },

        //get action binding
        GetTasksAction::class => function (ContainerInterface $c) {
            return new GetTasksAction(
                $c->get(LoggerInterface::class),
                $c->get(TaskRepository::class)
            );
        },

        UpdateTaskAction::class => function (ContainerInterface $c) {
            return new UpdateTaskAction(
                $c->get(LoggerInterface::class),
                $c->get(TaskRepository::class)
            );
        },

        DeleteTaskAction::class => function (ContainerInterface $c) {
            return new DeleteTaskAction(
                $c->get(LoggerInterface::class),
                $c->get(TaskRepository::class)
            );
        },
    ]);
};
