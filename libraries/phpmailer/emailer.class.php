<?php
include "class.phpmailer.php";

class Emailer {

	protected $host, $smtp, $port, $auth, $username, $password, $smtpSecure;

	public function __construct($host = "localhost", $smtp = false, $port = 25, $auth = false, $username = null, $password = null, $smtpSecure = "tls") {
	
		$this->host = $host;
		$this->smtp = $smtp;
		$this->port = $port;
		$this->auth = $auth;
		$this->username = $username;
		$this->password = $password;
		$this->smtpSecure = $smtpSecure;
	
	}
	
	public function create() { 
	
		$mailer = new \PHPMailer;
		
		$mailer->Host = $this->host;           
		$mailer->Port = $this->port;    
		
		if($this->smtp) 
		{
			$mailer->isSMTP();
		
			if($this->auth) {
			
				$mailer->SMTPAuth = true;                              
				$mailer->Username = $this->username;      
				$mailer->Password = $this->password;         
				$mailer->SMTPSecure = $this->smtpSecure;    
			}
		}
		else
		{
			$mailer->isMail();
		}
		
		$mailer->IsHTML(true);
		
		return $mailer;
	}

}

