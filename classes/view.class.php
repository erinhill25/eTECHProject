<?php
namespace Etech\Classes;

class View {
    
    protected $file, $vars = array();
    
    public function __construct($file) {
    
        $this->file = $file;

    }


    public function push($var, $value) {
    
        $this->vars[$var] = $value;
    
    }
    
    public function setFile($model, $method) {
    
        $this->file = $model . "/" . $method . ".php";
    
    }
    
    public function render() {
    
        extract($this->vars);
        
        ob_start();
		
        require "views/" . $this->file;
		$content = ob_get_contents();
		
        ob_end_clean();
		
        return $content;
    
    }
}