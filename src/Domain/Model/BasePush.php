<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Domain\Model;
/**
 * Description of BasePush
 *
 * @author Slavko
 */
class BasePush implements \JsonSerializable {
    
    /**
     * var string
     */
    protected $pushkey;
    
    /**
     * var Metric[]
     */
    protected $metrics;
    
    public function __construct(string $pushkey, $metrics) {
       $this->pushkey = $pushkey;
       $this->metrics = $metrics;
       
    }
    
    public function getPushkey() {
        return $this->pushkey;
    }

    public function getMetrics() {
        return $this->metrics;
    }

    public function setPushkey($pushkey): void {
        $this->pushkey = $pushkey;
    }

    public function setMetrics($metrics): void {
        $this->metrics = $metrics;
    }

        
    public function jsonSerialize() {
        return ["pushkey" => $this->pushkey, "metrics" => $this->metrics];
    }
}
