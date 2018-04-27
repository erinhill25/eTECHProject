<?php
namespace Etech\Classes;

class Response {
    
    protected $user, $template, $title, $class, $method, $direct=false, $JSON = false;
    
    public function __construct($user) {
    
        $this->user = $user;
        
        $this->template = new View("template.php");
    
    }

    public function setTemplate($file) {

        $this->template = new View($file);

    }
    
    public function setTitle($title) {
    
        $this->title = $title;
    
    }
    
    public function setClass($class) {
    
        $this->class = $class;
    
    }
    
    public function setMethod($method) {
    
        $this->method = $method;
    
    }
    
    public function setJSON() {
    
        $this->JSON = true;
    
    }
    
    public function setDirect() {
        $this->direct = true;
    }
    
    public function push($var, $value) {
        
        $this->template->push($var, $value);
    
    }
    
    public function render($content) {
         if($this->direct) {
            echo $content;
            return;
        }
        if($this->JSON) {
            header("content-type: application/javascript");
            echo $content;
            return;
        }
    
        $this->template->push("title", $this->title);
        
        $this->template->push("content", $content);
        $this->template->push("userID",  $this->user->getUserID());
        $this->template->push("firstname",  $this->user->getAttribute("Firstname"));
        $this->template->push("lastname",  $this->user->getAttribute("Lastname"));
        $this->template->push("model", $this->class);
        $this->template->push("method", $this->method);
        
        echo $this->template->render();
    
    }



}