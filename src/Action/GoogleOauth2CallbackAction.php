<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Action;
use Symfony\Component\HttpFoundation\Session\Session;
use Google\Client as Google_Client;
use Slim\Routing\RouteContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use App\Action\HomeAction;

/**
 * Description of Oauth2CallbackAction
 *
 * @author Slavko
 */
class GoogleOauth2CallbackAction {
    private $logger;

    /**
     * @var Session
     */
    private $session;
 
    private $settings;
    
    /**
     *
     * @var Twig
     */
    private $twig;
            
    public function __construct(LoggerInterface $logger,  Session $session, Twig $twig) {
         $this->logger = $logger;
         $this->session = $session;
         $this->twig = $twig;
       
    }
    public function __invoke(ServerRequestInterface $request,ResponseInterface $response ): ResponseInterface {
        $this->logger->info(__CLASS__.':'.__FUNCTION__);
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        
        $google = new Google_Client();
        $google->setClientId('853566220918-6rf0l78i8lbvlfjkfbn4vl35fodsvmha.apps.googleusercontent.com');
        $google->setClientSecret('tSytrr0xukjyTmHvfnVLE8wc');
        $google->setRedirectUri('https://'.$_SERVER['HTTP_HOST'].$routeParser->urlFor(GoogleOauth2CallbackAction::class));
        
        $code = $request->getQueryParams()['code'];
        
        if(isset($code)){
            $token = $google->fetchAccessTokenWithAuthCode($code);
            if(!isset($token['error'])){
                $this->session->set('access_token', $token['access_token']);
                return $response->withStatus(307)->withHeader('location',$routeParser->urlFor('googleaction'));
            }
            else {
               return  $response->withStatus(307)->withHeader('Location', $routeParser->urlFor(HomeAction::class));  
            }
        }
        else{
            return  $response->withStatus(307)->withHeader('Location', $routeParser->urlFor(HomeAction::class));
        }
        
    }
}
