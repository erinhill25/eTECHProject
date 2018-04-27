<?php
namespace Etech\Models;

include $_SERVER['DOCUMENT_ROOT'] . "/aws/aws-autoloader.php";

use Etech\Classes\Flasher AS Flasher;
use Etech\Classes\EmailTemplate AS EmailTemplate;
use Etech\Classes\Imaging AS Imaging;
use Aws\S3\S3Client;

class Profile extends Model {
    
    protected $s3Client;

    public function __construct($db, $response, $user, $mailer, $logger, $app) {
        parent::__construct($db, $response, $user, $mailer, $logger, $app);
  
        $validated = $this->db->query("SELECT EmailVerified FROM Users WHERE StarID=:UserID", array("UserID" => $this->user->getUserID()), "SINGLE");
      
        $this->s3Client = S3Client::factory(array(
            'credentials' => array(
                'key'    => AWS_KEY,
                'secret' => AWS_SECRET,
                ),
            'region' => AWS_REGION,
            'version' => 'latest'
        ));

        $this->s3Client->registerStreamWrapper();

        if(is_object($validated)) {
            $this->response->push("validated", $validated->EmailVerified);
        }
      
    }
    
    
    public function run() {
   
        $this->view->push("user", $this->user);
        

    }
    
    public function main() {
        
        $this->response->setTitle("Update Profile");

        $csrf = bin2hex(openssl_random_pseudo_bytes(24));
        setcookie("csrftoken", $csrf, time()+60*60*3, "/");

        $campuses = $this->db->query("SELECT Code, Campus FROM Campuses");
        
        $this->view->push("campuses", $campuses);
        $this->view->push("csrf", $csrf);
        $this->view->push("email", $this->user->getAttribute("Email"));
        $this->view->push("campusCode", $this->user->getAttribute("HomeCampus"));
    }
    
    protected function validation($email, $userID) {
            
        $token = hash( 'sha256', $userID . $email . PRIVATEKEY );
        
        $template = new EmailTemplate("validateEmail");
        $template->push("token", $token);
        $template->push("email", $email);
        $template->push("userID", $userID);
        
        $mail = $this->mailer->create();
        $mail->From = "info@rctclearn.net";
        $mail->FromName = "info@rctclearn.net";
        $mail->AddAddress($email);     
        $mail->AddReplyTo("info@rctclearn.net", "Information");
        $mail->Subject = "Validate your Email - eTECH Portal";
        $mail->Body = $template->render();
        
        $mail->send();    
    
    }
    
    public function sendValidation() {
        
        $email = $this->user->getAttribute("Email");
        if(!$email) {
         
             Flasher::set("message", array("class" => "failure", "message" => "No email has been set. Please use the profile form to update your email address"));
             $this->main();
             $this->view->setFile("profile", "main");
             
             return;
        
        }
         
       $this->validation($email, $this->user->getUserID());
       
        Flasher::set("message", array("class" => "success", "message" => "Thank you, an email has been sent to your email with instructions on how to validate."));
        
        $this->main();
        $this->view->setFile("profile", "main");
    }
    
    public function validate() {
        
        $this->response->setTitle("Validate Email");
        
        $userID = $_GET['userID'];
        
        $email = $_GET['email'];
        
        $token = $_GET['token'];
        
        $hash = hash( 'sha256', $userID . $email . PRIVATEKEY );
        
        $this->response->push("hideEmailNotification", true);
    
        if($token != $hash || $userID != $this->user->getUserID()) {
            
             Flasher::set("message", array("class" => "failure", "message" => "Invalid email link. Please contact support if this issue persists."));
             
             return;
        }
        
        $markValidated = $this->db->query("UPDATE Users SET EmailVerified = 1 WHERE StarID=:UserID", array("UserID" => $this->user->getUserID()));
        
        Flasher::set("message", array("class" => "success", "message" => "Thank you, your email has been validated and you are all set!<br />Redirecting you..."));
        
        $this->view->push("success", true);
    
    }
    
    public function update() {
        
        $this->response->setJSON();

        $email = $_POST['email'];
        $campus = $_POST['campus'];
  
        $errors = array();
        
        if(!isset($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address";
        }
        
        if(!isset($campus) || !$campus) {
        
            $errors[] = "Please indicate your degree seeking campus";
            
        }

        if(!isset($_COOKIE['csrftoken']) || ($_POST['csrftoken'] != $_COOKIE['csrftoken'])) {

            $errors[] = "Invalid request, please try again";

        }

        setcookie("csrftoken", null, 1, "/");
        
        if(count($errors) != 0) {
            
            return json_encode(array("errors" => true, "message" => "<strong>Profile not updated, errors found</strong>:<br />" . implode("<br />", $errors)));
        }
        
        $messages = array();
        //Is this a new email address and do we need to validate it?
        if($this->user->getAttribute("Email") != $email) {
            
            //Send Email Check
            
            $this->db->query("UPDATE Users SET EmailVerified=0 WHERE StarID=:UserID", array("UserID" => $this->user->getUserID()));

            $this->validation($email, $this->user->getUserID());
            
            $messages[] = "Email updated, you will receive an email with a link to validate this address as a working email";
        }
        
        $this->db->query("UPDATE Users SET Email=:Email, HomeCampus=:Campus WHERE StarID=:UserID", array("UserID" => $this->user->getUserID(), "Email" => $email, "Campus" => $campus));
        
        return json_encode(array("success" => true, "message" => "Successfully updated your settings<br />" . implode("<br />", $messages)));
        
    }
    
    public function changeAvatar() {

        $json = $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'; 
        
        if($json) {
            $this->response->setJSON();
        } else {
            $this->response->setDirect();
        }
        
        $file = $_FILES["upload"];
        if(isset($file) && !empty($file["tmp_name"])) {
            $requestName = basename($file["name"]);
            $fileType = isset($file['fileType']) ? $file['fileType'] : pathinfo($requestName, PATHINFO_EXTENSION);
            $check = getimagesize($file["tmp_name"]);
        }
        
        $errors = array();
        
      
        if(empty($file["tmp_name"])) {
        
            $errors[] = "Please choose a file";
        
        }
        
        if($fileType && !in_array(strtolower($fileType), array("gif", "png", "jpg", "jpeg"))) {
        
            $errors[] = "Invalid file extension";
        
        }
        
        if ($file["size"] && $file["size"] > 2 * (1000 * 1000)) {
                
                $errors[] = "Please limit the size of your avatar to below 2 megabytes";
            
        }
        if(!empty($file["tmp_name"]) && !$check) {
            
            $errors[] = "Invalid file type";
        
        }
        
        if(count($errors) > 0) {
     
              return json_encode(array("message" => "Avatar not changed, errors found", "errors" => $errors));

        }
        
        
        //Destination File
        $random = str_replace(".", "", uniqid(rand(), true));
        $filename = $random . '.' . $fileType;
        $destination = "s3://rctclearnsite/etech/uploads/";
     
        move_uploaded_file($file["tmp_name"], $destination . $filename);
        
        
        $img = new Imaging;
        $img->set_img($destination . $filename);
        $img->set_size(150);
        $img->save_img($destination . $filename);

        $finalDestination = "https://s3.amazonaws.com/rctclearnsite/etech/uploads/" . $filename;
        
        $this->db->query("UPDATE Users SET Avatar=:Avatar WHERE StarID=:UserID", array("Avatar" => $finalDestination, "UserID" => $this->user->getUserID()));
        
        return json_encode(array("message" => "Avatar successfully Updated", "image" => $finalDestination));
        

    }

    


}