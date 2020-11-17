<?php

use Slim\App;
use Slim\Middleware\ErrorMiddleware;
use Selective\BasePath\BasePathMiddleware;

use Slim\Views\TwigMiddleware;
use App\Middleware\SessionMiddleware;

return function (App $app) {
    $app->addRoutingMiddleware();
    $app->addBodyParsingMiddleware();
    $app->add(SessionMiddleware::class); 
    $app->add(TwigMiddleware::class);
    $app->add(BasePathMiddleware::class); // 
    // Catch exceptions and errors
    $app->add(ErrorMiddleware::class);
};
