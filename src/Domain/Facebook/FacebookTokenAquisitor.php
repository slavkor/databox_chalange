<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Domain\Facebook;
use \App\Domain\Model\TokenAquisitor;

use Facebook\Facebook;

/**
 * Description of FacebookTokenAquisitor
 *
 * @author Slavko
 */
final class FacebookTokenAquisitor extends TokenAquisitor{
    
    /**
     * 
     * @param string $code
     * @param string $redirect_url
     * @return string
     * 
     * Returns an access token based on a code from
     */
    public function AquireToken(string $code, string $redirect_url): string {
        if(!isset($this->settings)){
            echo 'missing facebook client settings';
            exit;
        }
        $facebook = new Facebook([
            'app_id' => $this->settings['facebook']['client_id'],
            'app_secret' => $this->settings['facebook']['client_secret'],
            'default_graph_version' => 'v8.0'
        ]);
        
        $oauthClient = $facebook->getOAuth2Client();
        try {
            $accessToken = $oauthClient->getAccessTokenFromCode($code, $redirect_url);
            if (!$accessToken->isLongLived()) {          
                // Exchanges a short-lived access token for a long-lived one
                $accessToken = $oauthClient->getLongLivedAccessToken($accessToken);
            }
            return $accessToken;
        } catch(Facebook\Exception\ResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exception\SDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }     
    }

}
