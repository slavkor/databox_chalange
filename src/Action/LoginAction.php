<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Action;
use Symfony\Component\HttpFoundation\Session\Session;

use Slim\Routing\RouteContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use Psr\Container\ContainerInterface;


use App\Domain\Facebook\FacebookTokenAquisitor;
use App\Domain\Google\GoogleTokenAquisitor;
use App\Action\PushAction;

/**
 * Description of LoginAcrtion
 *
 * @author Slavko
 */
class LoginAction{
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
    
    public function __construct(ContainerInterface $container, LoggerInterface $logger,  Session $session, Twig $twig) {
         $this->logger = $logger;
         $this->session = $session;
         $this->twig = $twig;
         $this->container = $container;
    }
    
    public function __invoke(ServerRequestInterface $request,ResponseInterface $response ): ResponseInterface {
        $this->logger->info(__CLASS__.':'.__FUNCTION__); 
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();    
        
        // get the referer - to distinguish who is calling the login
        $referer_host = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
        
        switch ($referer_host) {
            case 'accounts.google.com':
                //react to login via google
                $tokenAquisitor = new GoogleTokenAquisitor();
                $tokenAquisitor->setSettings($this->container->get('settings'));

                $accessToken = $tokenAquisitor->AquireToken($request->getQueryParams()['code'], 'https://'.$_SERVER['HTTP_HOST'].$routeParser->urlFor(LoginAction::class));
                $origin = 'google';
                break;
            case 'www.facebook.com':
                //react to login via facebook
                $tokenAquisitor = new FacebookTokenAquisitor();
                $tokenAquisitor->setSettings($this->container->get('settings'));
                $accessToken = $tokenAquisitor->AquireToken($request->getQueryParams()['code'], 'https://'.$_SERVER['HTTP_HOST'].$routeParser->urlFor(LoginAction::class));
                $origin = 'facebook';
                break;
            default:
                // unable to recognize the referer
                /// TODO throw exception
                break;
        }
        
        if(isset($accessToken)){
            $this->session->set('access_token', $accessToken);
            $this->session->set('origin', $origin);
            
            return $response->withStatus(307)->withHeader('Location',$routeParser->urlFor(PushAction::class));
        }
        else{
            return $response->withStatus(307)->withHeader('Location',$routeParser->urlFor(HomeAction::class));
        }
    }
}
