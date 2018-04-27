<?php
error_reporting(E_ALL);

require 'db_connect.php';
require 'helperfunctions.php';

require "libraries/phpmailer/class.phpmailer.php";

require '../starpro/auth/simplesamlphp/lib/_autoload.php';

require "definitions.php";

mb_internal_encoding("UTF-8");

date_default_timezone_set('America/Chicago');

function isSSL() {

    if(isset($_SERVER['https']) || isset($_SERVER['HTTPS'])) 
        return true;
    

    if(!empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ) 
        return true;
        
    return false;
}

if(isSSL()) {
    $_SERVER['HTTPS'] = true;
    $_SERVER['SERVER_PORT'] = 443;
}

if( !isSSL() && getenv('force_ssl') == true ) {
    
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);

    exit();
}

//Redirect users 
if(!empty($_COOKIE['redirectURL']) && strpos($_SERVER['REQUEST_URI'], "lti") === FALSE ) {

    setcookie("redirectURL", null, 1, "/", ".starpro.me");
    header("Location: " . $_COOKIE['redirectURL']);
   
    exit();
}


//Include class when needed
spl_autoload_register(function ($class) {
    
    $split = explode("\\", $class);
    $class = end($split);

    //Only handle etech classes
    if($split[0] != 'Etech') {
        return false;
    }

    if(file_exists(SITE_ROOT . '/models/' . strtolower($class) . '.class.php'))
    {
        include SITE_ROOT . '/models/' . strtolower($class) . '.class.php';
        return;
    }
    
    if(file_exists(SITE_ROOT . '/classes/' . strtolower($class) . '.class.php'))
    {
        include SITE_ROOT . '/classes/' . strtolower($class) . '.class.php';
        return;
    }
    
});


$db = new Etech\Classes\Database();

$as = new \SimpleSAML_Auth_Simple('default-sp');

//$as = new Etech\Classes\AuthMock();

$logger = new Etech\Classes\Logging($db);

$impersonateID = isset($_COOKIE['impersonateID']) ? $_COOKIE['impersonateID'] : null;

//Determine if impersonater has rights to do this
if(!empty($impersonateID)) {
    $standardUser = new Etech\Classes\User($db, $as, $logger);
    if($standardUser->getAttribute("Role") != "admin") {
        setcookie("impersonateID", null, time() - 3600, "/");
        $impersonateID = null;
    }
}

$db->query("set time_zone = '-05:00'");

$user = new Etech\Classes\User($db, $as, $logger, $impersonateID);

$response = new Etech\Classes\Response($user);

$mailer = new Etech\Classes\Emailer("smtp.postmarkapp.com", true, 587, true, EMAIL_USERNAME, EMAIL_PASSWORD);

$app = new Etech\Classes\Application($db, $user, $response, $mailer, $logger);

$app->run();

?>