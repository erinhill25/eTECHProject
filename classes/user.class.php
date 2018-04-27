<?php
namespace Etech\Classes;

class User {
    
    protected $db, $as, $impersonateID, $colors, $user, $shibAttributes, $attributes;

    public function __construct($db, $as, $logger, $impersonateID = null) {
        
        $this->db = $db;
        
        $this->as = $as;

        $this->logger = $logger;
   
        $this->defineColors();
        
        $this->impersonateID = $impersonateID;
    
        if ($this->as->isAuthenticated()) {

            $this->getUserAttributes();
          
            
        }
    

    }
    
    public function getImpersonateID() {
        
        return $this->impersonateID;
    
    }
    
    
    public function isAuthenticated() {
        
        return $this->as->isAuthenticated();
    
    }
    
    public function requireAuth() {
    
        if (!$this->as->isAuthenticated()) {
            $protocol = isSSL() ? 'https' : 'http';
            $url = $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

            setcookie("redirectURL", $url, time() + 3600, "/", ".starpro.me");

            header("location: " . AUTH_URL ."?redirect=".$protocol.'://'.$_SERVER['HTTP_HOST']);

            exit();
        }
    }
    
    public function defineColors() {
        $this->colors = array(
            "A" => "#87EB8C",
            "B" => "#FFD197",
            "C" => "#97F6FF",
            "D" => "#E9F25F",
            "E" => "#FF9797",
            "F" => "#97A3FF",
            "G" => "#C797FF",
            "H" => "#7B94FB",
            "I" => "#EDCD36",
            "J" => "#5DE35A",
            "K" => "#7EC9FA",
            "L" => "#C15757",
            "M" => "#5769C1",
            "N" => "#C157A7",
            "O" => "#57C190",
            "P" => "#C15792",
            "Q" => "#7DA8C2",
            "R" => "#727272",
            "S" => "#E68F14",
            "T" => "#5AC57B;",
            "U" => "#C6C9FF",
            "V" => "#FE0010",
            "W" => "#00FEC2",
            "X" => "#E31AE7",
            "Y" => "#52A5CF",
            "Z" => "#60B3B3"
        ); 
    }
    
    private function getUserAttributes() {
    
        $this->shibAttributes = $this->as->getAttributes();

        $this->user = (isset($this->impersonateID)) ? $this->impersonateID : $this->shibAttributes['urn:oid:0.9.2342.19200300.100.1.1'][0]; 
        
        if(!$this->user) {
            
            $this->as->logout();
            throw new \Exception("Identity provider has returned invalid attributes");
        
        }
        
 
        $this->attributes = $this->db->query("SELECT UserID, Firstname, Lastname, HomeCampus, Email, Role, Avatar FROM Users WHERE StarID=:UserID LIMIT 1", array("UserID" => $this->user), "SINGLE");
   
        if(!$this->attributes && !$this->impersonateID) {
            
            //Parse the display name
            $displayName = explode(" ", $this->shibAttributes['urn:oid:2.16.840.1.113730.3.1.241'][0]);
            $email = $this->shibAttributes['urn:oid:0.9.2342.19200300.100.1.3'][0];
            
            $newUser = $this->db->insert("Users", array("StarID" => $this->user, "Firstname" => $displayName[0], "Lastname" => $displayName[1], "Email" => $email, "Role" => "student", "DateRegistered" => getDateTime()));
            $this->logger->logEvent($this->user, "Account Creation");

            if(isset($this->shibAttributes['https://starid.mnscu.edu/shibboleth/attributes/mnscuscopedtechid'])) 
            {
                $techIDs = $this->shibAttributes['https://starid.mnscu.edu/shibboleth/attributes/mnscuscopedtechid'];
                foreach($techIDs AS $techID) {
                    
                    $data = explode("@", $techID);
                    
                    $query = $this->db->insert("TechIDs", array("UserID" => $this->user, "TechID" => $data[0], "Campus" => $data[1]));
                
                }
                
            }

        }

        //If login cookie is set, mark user as logging in
        if(isset($_COOKIE['login'])) {
            setcookie("login", 0, time() - 3600, "/", ".starpro.me");
            $this->logger->logEvent($this->user, "Logged In");
            $this->db->query("UPDATE Users SET LastLogin=:Date WHERE StarID=:UserID LIMIT 1", array("Date" => getDateTime(), "UserID" => $this->user)); 
        }
        
        if(isset($newUser)) {             
            header("location: " . SITE_PATH . "/profile");
        } 
       
    }
    
    public function getAvatar($userID = null) {
        
        if($userID) {
            
            $user = $this->db->query("SELECT Firstname, Avatar FROM Users WHERE StarID=:UserID", array("UserID" => $userID), "SINGLE");
            $firstname = $user->Firstname;
            $avatar = $user->Avatar;
            
        } else {
        
            $firstname = $this->getAttribute("Firstname");
            $avatar = $this->getAttribute("Avatar");
            
        }
        
        if($avatar) {
        
            return "<img src='" . $avatar . "' class='avatar' alt=\"" . $firstname . "'s Avatar\" />";
        
        }
        
        $letter = $firstname[0];
        $iconColor = !empty($this->colors[$letter]) ? $this->colors[$letter] : "#FF9797";
        
        return "<div class='usericon' style='background-color:" . $iconColor .";'>" . $letter . "</div>";
    
    }
    
    public function getUserID() {
    
        return $this->user;
    
    }
    
    public function getAttributes() {
    
        return $this->attributes;
    
    }
    
    public function getAttribute($attribute) {

        if(isset($this->attributes->$attribute)) {
        
            return $this->attributes->$attribute;
        
        }
        
        return false; 
    }
    
    

}