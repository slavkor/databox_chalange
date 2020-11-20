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
use Facebook\Facebook;
use Google\Client as Google_Client;
use App\Action\LoginAction;
use Slim\Views\Twig;
use Psr\Container\ContainerInterface;

/**
 * Description of HomeAction
 *
 * @author Slavko
 */
class HomeAction {
    
    
    /**
     *
     * @var LoggerInterface 
     */
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
    
    /**
     *
     * @var ContainerInterface 
     */
    private $container;
    
    public function __construct(ContainerInterface $container, LoggerInterface $logger, Session $session, Twig $twig) {
         $this->logger = $logger;
         $this->session = $session;
         $this->twig  = $twig;
         $this->container = $container;
    }
    public function __invoke(ServerRequestInterface $request,ResponseInterface $response ): ResponseInterface {
        $this->logger->info(__CLASS__.':'.__FUNCTION__);
        
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $settings = $this->container->get('settings');
        
        //facebook login
        $facebook = new Facebook([
            'app_id' => $settings['facebook']['client_id'],
            'app_secret' => $settings['facebook']['client_secret'],
            'default_graph_version' => 'v8.0'
        ]);
        $helper = $facebook->getRedirectLoginHelper();
        $fb_login_url = $helper->getLoginUrl('https://'.$_SERVER['HTTP_HOST'].$routeParser->urlFor(LoginAction::class), ['read_insights', 'pages_show_list']);

        
        //google login
        $google = new Google_Client();
        $google->setClientId($settings['google']['client_id']);
        $google->setClientSecret($settings['google']['client_secret']);
        $google->setRedirectUri('https://'.$_SERVER['HTTP_HOST'].$routeParser->urlFor(LoginAction::class));
        $google->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $google->setAccessType('offline');
        $google->setApprovalPrompt('force');
        $google_login_url = $google->createAuthUrl();
        
        $this->session->start();
        
        return $this->twig->render($response, 'home.twig', [ 'fb_login_url' => $fb_login_url, 'google_login_url' => $google_login_url]);
    }
}
