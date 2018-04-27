<?php

namespace Etech\Classes;

require_once(SITE_ROOT . '/classes/LTI/LTI_Tool_Provider.php');

class Application {
    
    protected $db, $user, $response, $mailer, $logger;
    protected $model = array(), $modelClasses = array();

    public function __construct($db, $user, $response, $mailer, $logger) {
        
        $this->db = $db;
        $this->user = $user;
        $this->response = $response;
        $this->mailer = $mailer;
        $this->logger = $logger;

        $this->authExceptions = [

            "home" => ["main", "home", "getUserData", "forwarding", "resources", "isSynced"],
            "lti"  => ["*"]
        ];
     
    }
    
    private function parseModels() {
        
        $json = file_get_contents("models.json");
        $models = json_decode($json, true);

        foreach($models['models'] AS $modelName) {
            $modelName = "\\Etech\\Models\\" . strtolower($modelName); 
            $this->modelClasses[$modelName] = new $modelName($this->db, $this->response, $this->user, $this->mailer, $this->logger, $this);
        }
   
    }
    
    public function get($model) {

        return $this->modelClasses[$model];
    
    }
    
    private function setModel($request) {
       
        $class = $request[0];
        $className = "\\Etech\\Models\\" . $class;
  
        $this->model = array(
            "class" => class_exists($className) ? $class : "Home",
            "className" => class_exists($className) ? $className : "\\Etech\\Models\\home"
        );
        
    }

    /*
        
        Given a request method and model, determine if shibboleth authentication is necessary based on exceptions list

    */
    private function determineAuth($model, $method) {

        foreach($this->authExceptions AS $exModel=>$exMethods)
        {

            if($exModel != $model) {
                continue;
            }

            if(in_array("*", $exMethods) || in_array($method, $exMethods)) {
                return;
            }

        }

        $this->user->requireAuth();
    }
   
    public function run() {
     
        //Parse models.json for model classes to load
        $this->parseModels();

        $request = str_replace(SITE_PATH, "", $_SERVER['REQUEST_URI']);
        $request = (substr($request, -1) == "/") ? substr($request, 0, -1) : $request;
        $request = explode("/", substr($request, 1));
     
        $this->setModel($request);

        //Requested model has name of $classname
        $this->model['instance'] = $this->modelClasses[$this->model['className']];

        $method = (isset($request[1])) ? $request[1] : "";
        
        $method = preg_replace("/([^?.]+)(\?(.+)?)?/", "$1", $method);

        if(!method_exists($this->model['instance'], $method)) {
         
            $method = "main";
         
        }
        

        $this->determineAuth(strtolower($this->model['class']), $method);
      
        $view = new View(strtolower($this->model['class']) . "/" . strtolower($method) . ".php");

        $this->model['instance']->setView($view);
        
        $this->model['instance']->run();
        
        foreach($request AS &$requestItem) {
            
            $requestItem = preg_replace("/([^?.]+)(\?(.+)?)?/", "$1", $requestItem);
        }

        //Call the model method with the parameters from the URL
        $data = call_user_func_array(array($this->model['instance'], $method), array_splice($request, 2));

        $content = !empty($data) ? $data : $view->render();

        $this->response->setClass($this->model['class']);
        $this->response->setMethod($method);
        $this->response->push("user", $this->user);
        $this->response->push("authenticated", $this->user->isAuthenticated());
        
        $this->response->render($content);
    
    }


}