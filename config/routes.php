<?php

use Slim\App;
use App\Domain\Databox\DataboxPush;
use App\Action\HomeAction;
use App\Action\FacebookGetOauthCallback;
use App\Action\FacebookAction;
use App\Action\GoogleAction;
use App\Action\GoogleOauth2CallbackAction;
use App\Action\LoginAction;
use App\Action\PushAction;
return function (App $app) {
    
    $app->get('/info', function($app){ phpinfo(); });
    
        $app->group('/databox', function($app){
            $app->get('/', HomeAction::class)->setName(HomeAction::class);
            $app->get('/login', LoginAction::class)->setName(LoginAction::class);
            $app->map(['GET', 'POST'], '/push', PushAction::class)->setName(PushAction::class)->add(DataboxPush::class);
        });
   
        $app->group('/fb', function($app){
            $app->get('/foa2cb', FacebookGetOauthCallback::class)->setName(FacebookGetOauthCallback::class);
            $app->map(['GET', 'POST'], 'push', FacebookAction::class)->setName('fbaction')->add(DataboxPush::class);
        });
        
        $app->group('/google', function($app){
            $app->map(['GET', 'POST'], '/push', GoogleAction::class)->setName('googleaction')->add(DataboxPush::class);
            $app->get('/goa2cb', GoogleOauth2CallbackAction::class)->setName(GoogleOauth2CallbackAction::class);
        });
};


    
    