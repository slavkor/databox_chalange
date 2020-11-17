<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Domain\Model;
/**
 * Description of Metric
 *
 * @author Slavko
 */
final class Metric implements \JsonSerializable{
    /**
     * var string
     */
    private $key;
    
    /**
     * var string
     */
    private $value;
    
    public function __construct(string $key, string $value) {
        $this->key = $key;
        $this->value = $value;
    }
    
    public function jsonSerialize() {
        return [
            "key" => $this->key,
            "value" => $this->value
        ];
    }
}
