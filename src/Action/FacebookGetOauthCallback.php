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
use Facebook\Facebook;
use Slim\Views\Twig;

use App\Action\FacebookAction;
/**
 * Description of FacebookGetOauthCallback
 *
 * @author Slavko
 */
class FacebookGetOauthCallback {
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
     * var Twig
     */
    private $twig;
    
    public function __construct(LoggerInterface $logger,  Session $session,  Twig $twig) {
         $this->logger = $logger;
         $this->session = $session;
         $this->twig = $twig;
    }
    public function __invoke(ServerRequestInterface $request,ResponseInterface $response ): ResponseInterface {
        $this->logger->info(__CLASS__.':'.__FUNCTION__);
        
        //$data = $this->facebook->GetAccessToken();
        
        $fb = new Facebook([
            'app_id' =>'1049210122167042',
            'app_secret' => '07e0756579f4f842bf8aad3fefaea723',
            'default_graph_version' => 'v8.0'
            ]);

        $helper = $fb->getRedirectLoginHelper();
        
        try {
            $accessToken = $helper->getAccessToken();
          } catch(Facebook\Exception\ResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
          } catch(Facebook\Exception\SDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
          }
          
        if (! isset($accessToken)) {
            if ($helper->getError()) {
              header('HTTP/1.0 401 Unauthorized');
              echo "Error: " . $helper->getError() . "\n";
              echo "Error Code: " . $helper->getErrorCode() . "\n";
              echo "Error Reason: " . $helper->getErrorReason() . "\n";
              echo "Error Description: " . $helper->getErrorDescription() . "\n";
            } else {
              header('HTTP/1.0 400 Bad Request');
              echo 'Bad request';
            }
            exit;
        }  
        
        $oAuth2Client = $fb->getOAuth2Client();
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        $tokenMetadata->validateAppId('1049210122167042');
        $tokenMetadata->validateExpiration();

        if (!$accessToken->isLongLived()) {          
          // Exchanges a short-lived access token for a long-lived one
          try {
            $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
          } catch (Facebook\Exception\SDKException $e) {
            echo "<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n";
            exit;
          }
        }
        
        $fb_response = $fb->get('/me/accounts?fields=access_token', $accessToken->getValue());

        $json = $fb_response->getBody();
        $data = json_decode($json, true);

            $myfile = fopen($this->settings['config'].'/facebook.access.json', "w");
            fwrite($myfile, $json);
            fclose($myfile); 
        $page = $data['data'][0];
        
        if(isset($page))
        {
            $this->session->set('access_token', $page['access_token']);
            $this->session->set('page_id', $page['id']);
        }

        
        if ($this->session->has('access_token')) {
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            return $response->withStatus(307)->withHeader('location',$routeParser->urlFor('fbaction'));
                    /*
            return $this->twig->render($response, 'fbpost.twig', [ 'access_token' => $this->session->get('access_token'), 
                'page_id'=>  $this->session->get('page_id'), 
                'metrics'=> 'page_views_total,page_engaged_users,page_actions_post_reactions_like_total,page_total_actions,page_consumptions' ]);
            */
        } else {

            return $response->withStatus(500);
        }
    }
}
