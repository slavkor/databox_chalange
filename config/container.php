<?php

use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;
use Selective\BasePath\BasePathMiddleware;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use App\Domain\Databox\DataboxPush;
use App\Domain\Facebook\FacebookMetrics;
use App\Domain\Google\GoogleMetrics;
use App\Domain\Repo\Tokens;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;


return [
    'settings' => function () {
        return require __DIR__ . '/settings.php';
    },
    LoggerInterface::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];

        $logger = new Logger($settings['name']);
        $handler = new StreamHandler($settings['path'], $settings['level']);
        $logger->pushHandler($handler);

        return $logger;
    },    
    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);
        return AppFactory::create();
    },
    ErrorMiddleware::class => function (ContainerInterface $container) {
        $app = $container->get(App::class);
        $settings = $container->get('settings')['error'];

        return new ErrorMiddleware(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            (bool)$settings['display_error_details'],
            (bool)$settings['log_errors'],
            (bool)$settings['log_error_details'], 
            $container->get(LoggerInterface::class)
        );
    },
            
    BasePathMiddleware::class => function (ContainerInterface $container) {
        return new BasePathMiddleware($container->get(App::class));
    },
    JsonMapper::class =>function (ContainerInterface $container) {
        $mapper = new JsonMapper();
        $mapper->setLogger($container->get(LoggerInterface::class));
        return $mapper;
    }, 

    DataboxPush::class =>function (ContainerInterface $container) {
        return new DataboxPush($container->get(LoggerInterface::class), $container->get(JsonMapper::class));
    }, 
    FacebookMetrics::class =>function (ContainerInterface $container) {
        $instance = new FacebookMetrics($container->get(LoggerInterface::class), $container->get(JsonMapper::class), $container->get('settings'));
        return $instance;
    }, 
    GoogleMetrics::class =>function (ContainerInterface $container) {
        return new GoogleMetrics($container->get(LoggerInterface::class), $container->get(JsonMapper::class), $container->get('settings'));
    },
    Session::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['session'];
        if (PHP_SAPI === 'cli') {
            return new Session(new MockArraySessionStorage());
        } else {
            return new Session(new NativeSessionStorage($settings));
        }
    },
    SessionInterface::class => function (ContainerInterface $container) {
        return $container->get(Session::class);
    },
     // Twig templates
    Twig::class => function (ContainerInterface $container) {
        $settings = $container->get('settings');
        $twigSettings = $settings['twig'];

        $options = $twigSettings['options'];
        $options['cache'] = $options['cache_enabled'] ? $options['cache_path'] : false;

        $twig = Twig::create($twigSettings['paths'], $options);

        // Add extension here
        // ...
        
        return $twig;
    },
    TwigMiddleware::class => function (ContainerInterface $container) {
        return TwigMiddleware::createFromContainer(
            $container->get(App::class),
            Twig::class
        );
    },
    Tokens::class => function(ContainerInterface $container){
        return new Tokens();
    }
            
            
];
