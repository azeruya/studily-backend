<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

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
