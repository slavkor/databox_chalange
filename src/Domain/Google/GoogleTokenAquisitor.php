<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Domain\Google;

use \App\Domain\Model\TokenAquisitor;
use Google\Client as Google;
/**
 * Description of GoogleTokenAquisitor
 *
 * @author Slavko
 */
final class GoogleTokenAquisitor extends TokenAquisitor{
    
    
    public function AquireToken(string $code, string $redirect_url): string {
        if(!isset($this->settings)){
            echo 'missing google client settings';
            exit;
        }
        
        $google = new Google();
        $google->setClientId($this->settings['google']['client_id']);
        $google->setClientSecret($this->settings['google']['client_secret']);
        $google->setRedirectUri($redirect_url);
        
        $token = $google->fetchAccessTokenWithAuthCode($code);
        return $token['access_token'];
        
    }

}
