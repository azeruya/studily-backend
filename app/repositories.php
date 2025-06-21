<?php

declare(strict_types=1);

use App\Domain\User\UserRepository;
use App\Infrastructure\Persistence\User\PostgresUserRepository;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use App\Domain\Task\TaskRepository;
use App\Infrastructure\Persistence\Task\PostgresTaskRepository;
use App\Domain\Study\StudyLogRepository;
use App\Infrastructure\Persistence\Study\PostgresStudyLogRepository;
use App\Domain\Character\CharacterRepository;
use App\Infrastructure\Persistence\Character\PostgresCharacterRepository;
use App\Application\Actions\Character\ListCharactersAction;
use App\Domain\Pomodoro\PomodoroSessionRepository;
use App\Infrastructure\Persistence\Pomodoro\PostgresPomodoroSessionRepository;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        UserRepository::class => function (ContainerInterface $c) {
            return new PostgresUserRepository($c->get(PDO::class));
        },
        TaskRepository::class => \DI\autowire(PostgresTaskRepository::class),
        StudyLogRepository::class => \DI\autowire(PostgresStudyLogRepository::class),
        CharacterRepository::class => \DI\autowire(PostgresCharacterRepository::class),
        PomodoroSessionRepository::class => function (ContainerInterface $c) {
            return new PostgresPomodoroSessionRepository(
                $c->get(PDO::class),
                $c->get(StudyLogRepository::class)
            );
        },
    ]);
};
