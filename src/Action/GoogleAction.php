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
use Slim\Routing\RouteContext;
use Slim\Views\Twig;
/**
 * Description of PushGoogleMetrics
 *
 * @author Slavko
 */
class GoogleAction {
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
  
        switch ($request->getMethod()) {
            case 'GET':
                return $this->twig->render($response, 'googlepost.twig', [ 'access_token' => $this->session->get('access_token'), 
                'view_id'=>  '232438052', 
                'metrics'=> 'ga:sessions,ga:users,ga:entrances,ga:pageviews,ga:bounces' ]);
            case 'POST';
  
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
