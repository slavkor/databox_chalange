<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Domain\Model;

/**
 * Description of TokenAquisitor
 *
 * @author Slavko
 */
abstract class TokenAquisitor {
    

    protected $settings;
    
    abstract public function AquireToken(string $code, string $redirect_url):string; 
    
    public function setSettings($settings) {
        $this->settings = $settings;
    }
}
