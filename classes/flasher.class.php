<?php
namespace Etech\Classes;

class Flasher {

    public static function set($key, $value) {
        
        $_SESSION[$key] = array("session" => $_SESSION['request'], "value" => $value);
        
    }
    
    public static function contains($key) {
        
        if(!isset($_SESSION[$key])) {
        
            return false;
        
        }
    
        return true;
    }
    
    public static function remove($key) {
    
        unset($_SESSION[$key]);
    
    }
    
    public static function get($key) {
    
        if(!isset($_SESSION[$key])) {
            
            throw new \Exception("This value has not been set or has been expired");
        
        }
        
        $value = $_SESSION[$key];
        
        if($value['session'] != $_SESSION['request']) {
            unset($_SESSION[$key]);
        }
        
        return $value['value']; 
    }
    
}