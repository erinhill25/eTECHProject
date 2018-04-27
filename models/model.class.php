<?php

namespace Etech\Models;

abstract class Model {
    
    protected $view, $response, $db, $user, $mailer, $logger, $app;
    
    public function __construct($db, $response, $user, $mailer, $logger, $app) {
        $this->db = $db;
        $this->response = $response;
        $this->user = $user;
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->app = $app;
    }
    
    public function setView($view) {
    
        $this->view = $view;
    
    }
    
    public function run() {
    
    }

}