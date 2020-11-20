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
use App\Domain\Google\GoogleMetrics;
use Symfony\Component\HttpFoundation\Session\Session;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;

/**
 * Description of PushAction
 *
 * @author Slavko
 */
class PushAction {
    private $logger;
    
    /**
     * var GoogleMetrics
     */
    private $google;

    /**
     * @var Session
     */
    private $session;
    

    /**
     *
     * @var Twig 
     */
    private $twig;
    
    public function __construct(LoggerInterface $logger, GoogleMetrics $google, Session $session, Twig $twig) {
         $this->logger = $logger;
         $this->google = $google;
         $this->session = $session;
         $this->twig = $twig;
    }
    
    public function __invoke(ServerRequestInterface $request,ResponseInterface $response ): ResponseInterface {
        $this->logger->info(__CLASS__.':'.__FUNCTION__);

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();    
        
        // we dont hawe access_token -> go to home screen to aquire one
        if(!$this->session->has('access_token')){
            return $response->withStatus(307)->withHeader('Location',$routeParser->urlFor(HomeAction::class));
        }
        
        switch ($request->getMethod()) {
            case 'GET':
                return $this->twig->render($response, 'push.twig', [ 'access_token' => $this->session->get('access_token'), 
                    'origin' => $this->session->get('origin'), 
                    'metrics_google'=> 'ga:sessions,ga:users,ga:entrances,ga:pageviews,ga:bounces',
                    'metrics_facebook'=> 'page_views_total,page_engaged_users,page_actions_post_reactions_like_total,page_total_actions,page_consumptions']);
            case 'POST';
  
                var_dump('aaa');die;
                if($this->session->has('access_token')){
                    $data = $this->google->GetGoogleAnalyticsMetrics($request->getParsedBody(), $this->session->get('access_token'));
                    return $response->withJson(json_encode($data));
                }
                else
                {        
                    return $response;
                }
        
            default:
                break;
        } 
    }   
}
