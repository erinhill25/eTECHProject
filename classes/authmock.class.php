<?php
namespace Etech\Classes;

class AuthMock {
    
    public function isAuthenticated() {
        return true;
    }
    
    public function getAttributes() {
        
        return array(
           'urn:oid:0.9.2342.19200300.100.1.1' => array('STARID')
        );
    }
}

