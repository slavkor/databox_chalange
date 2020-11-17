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
use App\Domain\Facebook\FacebookMetrics;
use Symfony\Component\HttpFoundation\Session\Session;
use Slim\Routing\RouteContext;
use App\Action\FacebookGetOauthCallback;
use Facebook\Facebook;
use Slim\Views\Twig;

/**
 * Description of FacebookAction
 *
 * @author Slavko
 */
class FacebookAction {
    private $logger;
    private $facebook;
    /**
     * @var Session
     */
    private $session;
    
    /**
     *
     * @var Twig 
     */
    private $twig;
    
    public function __construct(LoggerInterface $logger, FacebookMetrics $facebook, Session $session, Twig $twig) {
         $this->logger = $logger;
         $this->facebook = $facebook;
         $this->session = $session;
         $this->twig = $twig;
    }
    public function __invoke(ServerRequestInterface $request,ResponseInterface $response ): ResponseInterface {
        $this->logger->info(__CLASS__.':'.__FUNCTION__);
        
        switch ($request->getMethod()) {
            case 'GET':
                return $this->twig->render($response, 'fbpost.twig', [ 'access_token' => $this->session->get('access_token'), 
                'page_id'=>  $this->session->get('page_id'), 
                'metrics'=> 'page_views_total,page_engaged_users,page_actions_post_reactions_like_total,page_total_actions,page_consumptions' ]);
                
            case 'POST';
                $data = $this->facebook->GetPageMetrics($request->getParsedBody(), $this->session->get('access_token'));
                return $response->withJson(json_encode($data));
        
            default:
                break;
        }
        

    }
}
