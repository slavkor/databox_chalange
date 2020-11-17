<?php
namespace App\Domain\Google;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use Google\Client as Google_Client;
use Google_Service_AnalyticsReporting;
use Google_Service_AnalyticsReporting_DateRange;
use Google_Service_AnalyticsReporting_Metric;
use Google_Service_AnalyticsReporting_ReportRequest;
use Google_Service_AnalyticsReporting_GetReportsRequest;
use Psr\Log\LoggerInterface;
use JsonMapper;
use App\Domain\Model\Metric;
use App\Domain\Model\BasePush;

use App\Domain\Model\GoogleData;
/**
 * Description of GoogleMetrics
 *
 * @author Slavko
 */
final class GoogleMetrics {
 
    private $logger;
    /**
     *
     * @var JsonMapper
     */
    private $jsonMapper;
    
    private $settings;
    
    
    public function __construct(LoggerInterface $logger, JsonMapper $jsonMapper, $settings) {
         $this->logger = $logger;
         $this->jsonMapper = $jsonMapper;
         $this->settings = $settings;
    }
    
    
    public function GetGoogleAnalyticsMetrics($data, $token) {
        $this->logger->info(__CLASS__.':'.__FUNCTION__);

        //setup client 
        $client = new Google_Client();
        //$client->setApplicationName("Databox");
        //$client->setAuthConfig(settings['googleauthconfig']);
        $client->setAccessToken($token);
        //$client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        
  
        $analytics = new Google_Service_AnalyticsReporting($client);
  
        // Create the DateRange object.
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate("7daysAgo");
        $dateRange->setEndDate("today");

        $mm = explode(',', $data['metrics']);
        $metrics[] = array();
        foreach ($mm as $key) {
            $metric = new Google_Service_AnalyticsReporting_Metric();
            $metric->setExpression($key);
            $metric->setAlias($key);
            
            $metrics[] = $metric;
        }
        //var_dump($data);die;
        
        // Create the ReportRequest object.
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($data["view_id"]);
        $request->setDateRanges($dateRange);
        $request->setMetrics($metrics);

        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests( array( $request) );
        $reports = $analytics->reports->batchGet( $body );  
        

        
        //prepare the responses for DataboxPush middleware
        for ( $reportIndex = 0; $reportIndex < count( $reports ); $reportIndex++ ) {
            $report = $reports[ $reportIndex ];
            $header = $report->getColumnHeader();
            $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
            $rows = $report->getData()->getRows();
        
            for ( $rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
              $row = $rows[ $rowIndex ];
              
              $metrics = $row->getMetrics();
              
              for ($j = 0; $j < count($metrics); $j++) {
                $values = $metrics[$j]->getValues();
                
               
                for ($k = 0; $k < count($values); $k++) {
                  $entry = $metricHeaders[$k];
                  
                  $mtrcs[] = new Metric($entry->getName(),$values[$k] );
                }
              }
          
            }
        }

        // return to DataboxPush middleware
        return new BasePush($data['databoxtoken'], $mtrcs);

    }
}

