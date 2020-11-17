<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Action;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Slim\Routing\RouteContext;
use App\Action\FacebookGetOauthCallback;
use Facebook\Facebook;
use Google\Client as Google_Client;

use Slim\Views\Twig;

/**
 * Description of HomeAction
 *
 * @author Slavko
 */
class HomeAction {
    private $logger;
    /**
     * @var Session
     */
    private $session;
    
    /**
     *
     * @var Twig
     */
    private $twig;
    public function __construct(LoggerInterface $logger, Session $session, Twig $twig) {
         $this->logger = $logger;
         $this->session = $session;
         $this->twig  = $twig;
    }
    public function __invoke(ServerRequestInterface $request,ResponseInterface $response ): ResponseInterface {
        $this->logger->info(__CLASS__.':'.__FUNCTION__);

        
        /// TODO extract parameters from request
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        
        //facebook login
        $facebook = new Facebook([
            'app_id' =>'1049210122167042',
            'app_secret' => '07e0756579f4f842bf8aad3fefaea723',
            'default_graph_version' => 'v8.0'
        ]);
        $helper = $facebook->getRedirectLoginHelper();
        $fb_login_url = $helper->getLoginUrl('https://'.$_SERVER['HTTP_HOST'].$routeParser->urlFor(FacebookGetOauthCallback::class), ['read_insights', 'pages_show_list']);
  
        
        
        //google login
        $google = new Google_Client();
        $google->setClientId('853566220918-6rf0l78i8lbvlfjkfbn4vl35fodsvmha.apps.googleusercontent.com');
        $google->setClientSecret('tSytrr0xukjyTmHvfnVLE8wc');
        $google->setRedirectUri('https://'.$_SERVER['HTTP_HOST'].$routeParser->urlFor(GoogleOauth2CallbackAction::class));
        $google->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $google_login_url = $google->createAuthUrl();
        
        $this->session->start();
        
        return $this->twig->render($response, 'home.twig', [ 'fb_login_url' => $fb_login_url, 'google_login_url' => $google_login_url]);
    }
}
