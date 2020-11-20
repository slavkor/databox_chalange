<?php

namespace App\Domain\Databox;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;
use JsonMapper;
use App\Domain\Model\BasePush;

use Databox\Client as DataboxClient;

/**
 * Description of DataboxPush
 *
 * @author Slavko
 */
final class DataboxPush {
    private $logger;
    
    /**
     *
     * @var JsonMapper
     */
    private $jsonMapper;
    public function __construct(LoggerInterface $logger, JsonMapper $jsonMapper) {
         $this->logger = $logger;
         $this->jsonMapper = $jsonMapper;
    }
    
    /**
     * 
     *
     * @param  ServerRequest  $request PSR-7 request
     * @param  RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $this->logger->info(__CLASS__.':'.__FUNCTION__);
        
        //get data from source 
        $response = $handler->handle($request);
        if($request->getMethod() == 'GET'){
            return $response;
        }
        
        if ($response->getStatusCode() != 200) {
            return $response;
        }

        //parse data
        $data = json_decode(json_decode($response->getBody()), true);

        if(!isset($data['pushkey'])){
            return $response;
        }
   
        //push to client
        $client = new DataboxClient($data['pushkey']);

        foreach ($data['metrics'] as $metric) {
            $kpis[] = [$metric['key'], $metric['value']];
        }

        return $response->withJson($client->insertAll($kpis));

    }
}
