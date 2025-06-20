<?php

declare(strict_types=1);

use App\Domain\User\UserRepository;
use App\Infrastructure\Persistence\User\PostgresUserRepository;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use App\Domain\Task\TaskRepository;
use App\Infrastructure\Persistence\Task\PostgresTaskRepository;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        UserRepository::class => function (ContainerInterface $c) {
            return new PostgresUserRepository($c->get(PDO::class));
        },
        TaskRepository::class => \DI\autowire(PostgresTaskRepository::class),
    ]);
};
