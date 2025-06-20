<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Application\Middleware\JwtMiddleware;


return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->group('/auth', function (Group $group) {
        $group->post('/register', \App\Application\Actions\Auth\RegisterAction::class);
        $group->post('/login', \App\Application\Actions\Auth\LoginAction::class);
    });

   $app->group('/tasks', function (Group $group) {
        $group->get('', \App\Application\Actions\Task\GetTasksAction::class);
        $group->post('', \App\Application\Actions\Task\CreateTaskAction::class);
        $group->put('/{taskId}', \App\Application\Actions\Task\UpdateTaskAction::class);
        $group->delete('/{taskId}', \App\Application\Actions\Task\DeleteTaskAction::class);
    })->add(new JwtMiddleware($_ENV['JWT_SECRET']));

    $app->group('/study', function (Group $group) {
        $group->post('/log', \App\Application\Actions\Study\LogStudyAction::class);
    })->add(new JwtMiddleware($_ENV['JWT_SECRET']));


    //testing
    $app->get('/test-db', function ($request, $response, $args) {
        $settings = $this->get(\App\Application\Settings\SettingsInterface::class);
        $pdo = \App\Infrastructure\Persistence\Database::getConnection($settings);

        $stmt = $pdo->query("SELECT NOW()");
        $now = $stmt->fetchColumn();

        $response->getBody()->write("Connected! Current time: " . $now);
        return $response;
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });
};
