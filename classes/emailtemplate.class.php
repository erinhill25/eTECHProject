<?php
namespace Etech\Classes;

class EmailTemplate {

    protected $file, $vars = array();
    
    public function __construct($file) {
    
        $this->file = $file . ".php";

    }


    public function push($var, $value) {
    
        $this->vars[$var] = $value;
    
    }

    
    public function render() {
    
        extract($this->vars);
        
        ob_start();
		
        require "views/emailtemplates/" . $this->file;
		$content = ob_get_contents();
		
        ob_end_clean();
		
        return $content;
    
    }


}