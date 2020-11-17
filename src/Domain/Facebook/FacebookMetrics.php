<?php
namespace App\Domain\Facebook;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use Facebook\Facebook;
use Psr\Log\LoggerInterface;
use JsonMapper;
use App\Domain\Model\Metric;
use App\Domain\Model\BasePush;
use App\Domain\Facebook\TokenRepository;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Description of FacebookMetrics
 *
 * @author Slavko
 */
final class FacebookMetrics {
    //put your code here
    private $logger;
    /**
     *
     * @var JsonMapper
     */
    private $jsonMapper;
    
    private $settings;
    
    /**
     *
     * @var TokenRepository
     */
    private $repository;
    public function __construct(LoggerInterface $logger, JsonMapper $jsonMapper, $settings) {
         $this->logger = $logger;
         $this->jsonMapper = $jsonMapper;
         $this->settings = $settings;
         
    }
       
    public function GetPageMetrics($data, $token){
        $this->logger->info(__CLASS__.':'.__FUNCTION__);
 
        //set client 
        $facebook = new Facebook([
            'app_id' =>'1049210122167042',
            'app_secret' => '07e0756579f4f842bf8aad3fefaea723',
            'default_graph_version' =>'v8.0'
        ]);
        $facebook->setDefaultAccessToken($token);
   

        
        $mm = explode(',', $data['metrics']);
        try {
     
            // get the metrics using sendBatchRequest
            foreach ($mm as $key) {
                $requests[] =  $facebook->request('GET','/'.$data['page_id'].'/insights/'.$key.'/day');
            }
            
            $responses = $facebook->sendBatchRequest($requests, $token);
            
            
       } catch(Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
       } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
       }       
   
       
       //prepare the responses for DataboxPush middleware
       foreach ($responses as $key => $response) {
            if (!$response->isError()) {
                $arr = $response->getGraphEdge()->asArray();
                $metrics[] = new Metric($arr[0]['name'],$arr[0]['values'][0]['value']);
            }
            else
            {
                var_dump($response->getDecodedBody());
            }
       }

   
       // return to DataboxPush middleware
       return new BasePush($data['databoxtoken'], $metrics);
    }
}
