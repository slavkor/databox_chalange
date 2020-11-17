<?php
namespace App\Domain\Repo;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Tokens
 *
 * @author Slavko
 */
class Tokens {
    public function SaveToken($id, $value){
        $myfile = fopen($this->settings['config'].'/'.$id, "w");
        fwrite($myfile, $value);
        fclose($myfile); 
        return $value;
    }
    public function FetchToken($id) {
        try {
            $myfile = fopen($this->settings['config'].'/'.$id, "r");
            $token = fread($myfile, filesize($this->settings['config'].'/'.$id));
            fclose($myfile); 
            return $token;
        } catch (\Exception $ex) {
            $this->logger->error($ex->getMessage());
            exit;
        }
    }
}
