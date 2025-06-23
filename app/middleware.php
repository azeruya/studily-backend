<?php
use App\Application\Middleware\CorsMiddleware;
use Slim\App;

return function (App $app) {
    $app->add(CorsMiddleware::class); 
};
