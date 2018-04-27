<?php

namespace Etech\Models;

use Etech\Classes\Flasher AS Flasher;
use Etech\Classes\EmailTemplate AS EmailTemplate;

include SITE_ROOT . "/classes/HtmlPurifier/HTMLPurifier.standalone.php";

class Messages extends Model {
    
    protected $db, $response, $purifier, $unread;
    
    public function __construct($db, $response, $user, $mailer, $logger, $app) {
        parent::__construct($db, $response, $user, $mailer, $logger, $app);
        
        $config = \HTMLPurifier_Config::createDefault();
        $this->purifier = new \HTMLPurifier($config);
        
        $this->unread = $this->determineUnreadMessages();
        
        $this->response->push("unreadMessages", $this->unread);
        
    
    }
    
    public function run() {
        
        $this->view->push("unreadMessages", $this->unread);
        
        $terms = array();
   
        //Get Courses Enrollments
        if($this->user->getAttribute("Role") == "teacher") {
            $courses = $this->db->query("SELECT c.OrgID,c.Name,t.Code,t.Term FROM Courses AS c LEFT JOIN Terms AS t ON t.Code=c.YearTerm WHERE c.InstructorID=:UserID ORDER BY t.StartDate DESC,c.Name ASC", array("UserID" => $this->user->getUserID()));
        }
        if($this->user->getAttribute("Role") == "student" || $this->user->getAttribute("Role") == "admin") {
            $courses = $this->db->query("SELECT c.OrgID,c.Name,t.Code,t.Term FROM Enrollments AS e JOIN Courses AS c ON c.OrgID=e.CourseID LEFT JOIN Terms AS t ON t.Code = c.YearTerm WHERE e.StarID=:UserID ORDER BY t.StartDate DESC,c.Name ASC", array("UserID" => $this->user->getUserID()));
        }
        
        foreach($courses AS $course) {
            
            $course->unread = $this->db->query(
                        "SELECT count(*) AS unread 
                         FROM Conversations AS c
                         JOIN Recipients AS r ON r.ConversationID = c.ConversationID
                         WHERE r.CourseID=:Course
                         AND (('" . $this->user->getAttribute("Role") . "'!='teacher' AND r.Type !='Instructor') OR ('" . $this->user->getAttribute("Role") . "' = 'teacher' AND r.Type='Instructor'))
                         AND c.ConversationID NOT IN (SELECT ConversationID FROM Views WHERE UserID = :UserID AND Date >= (SELECT Date FROM Messages AS m WHERE m.ConversationID=c.ConversationID ORDER BY MessageID DESC LIMIT 1) )", 
                         array("Course" => $course->OrgID, "UserID" => $this->user->getUserID()), "SINGLE");

            
            if(!isset($terms[$course->Code])) {
                $terms[$course->Code] = array("Term" => $course->Term, "Code" => $course->Code, "Courses" => array());
            }
            $terms[$course->Code]['Courses'][] = $course;
            
        }
        
        $this->view->push("courses", $courses);
        $this->view->push("terms", $terms);
        
        $this->view->push("user", $this->user);
       
    
    }
 
    public function determineUnreadMessages($userID = null) {
 
        $user = isset($userID) ? $userID : $this->user->getUserID();
        $userData = $this->db->query("SELECT Role FROM Users WHERE StarID=:User", array("User" => $user), "SINGLE");
        if(!$userData) return 0;
    
        $unread = $this->db->query(
        "SELECT COUNT(*) AS unread
        FROM Conversations AS c 
        JOIN Users AS u ON u.StarID = c.StarterID 
        WHERE (c.ConversationID IN (SELECT ConversationID FROM Recipients WHERE UserID=:UserID) 
        OR c.StarterID =:UserID 
        OR c.ConversationID IN (SELECT r.ConversationID FROM Enrollments AS e JOIN Recipients AS r ON r.CourseID = e.CourseID WHERE e.StarID = :UserID AND r.Type='Course')
        OR c.ConversationID IN (SELECT r.ConversationID FROM Courses AS c JOIN Recipients AS r ON r.CourseID = c.OrgID WHERE c.InstructorID = :UserID AND r.Type='Instructor')
        OR ('" . $userData->Role . "' = 'student' AND 'allStudents' IN (SELECT UserID FROM Recipients AS r WHERE r.ConversationID=c.ConversationID)) 
        OR (('" . $userData->Role . "' = 'teacher' OR '" . $userData->Role . "'='admin') AND 'allStaff' IN (SELECT UserID FROM Recipients AS r WHERE r.ConversationID=c.ConversationID)))
        AND c.ConversationID NOT IN (SELECT ConversationID FROM Views WHERE UserID = :UserID AND Date >= (SELECT Date FROM Messages AS m WHERE m.ConversationID=c.ConversationID ORDER BY MessageID DESC LIMIT 1) ) 
        ORDER BY c.Date DESC", array("UserID" => $user), "SINGLE"); 
        
        return $unread->unread;
        
    }
    
    public function main($favorites = false, $orgID = 0) {
        
        $this->view->push("userID", $this->user->getUserID());

        if($this->user->getImpersonateID()) {
            Flasher::set("errorMessage", array("class" => "failure", "message" => "You are not allowed to view messages during impersonation"));
            return;

        }

        $this->view->push("canViewActivity", ($this->user->getAttribute("Role") == "admin" || $this->user->getAttribute("Role") == "teacher") ? true : false);

        $this->view->push("messages", $this->getMessages($favorites, $orgID)); 
        
        $page = "";
        
        if($orgID) {
        
            $page = $orgID;
        
        }
        
        if($favorites && $favorites != "all") {
        
            $page = "favorites";
        
        }
        
        if(!$page) {
        
            $page = "inbox";
        
        }
        
        $this->view->push("page", $page);
    }
    
    
    public function getMessages($favorites = false, $orgID = 0) {
        
        $this->response->setTitle("View Messages");
        
        $addition = "";
        if($favorites != "all" && $favorites) {
            
            $this->response->setTitle("View Favorites");
            $addition = "c.ConversationID IN (SELECT ConversationID FROM Favorites WHERE UserID=:UserID) AND ";
        
        }
        
        //Messages from a course
        if($orgID != 0) {
            
            $course = $this->db->query("SELECT Name FROM Courses WHERE OrgID=:CourseID", array("CourseID" => $orgID), "SINGLE");
            if(!$course) {
                return array();
            }
            $this->response->setTitle("View Messages for " . $course->Name);
            $messages = $this->db->query(
            "SELECT c.StarterID, c.ConversationID, (SELECT Date FROM Messages AS m WHERE m.ConversationID=c.ConversationID ORDER BY MessageID DESC LIMIT 1) AS LastMessageDate, c.Title, u.StarID, u.Firstname, u.Lastname, u.Role
            FROM Conversations AS c 
            JOIN Users AS u ON u.StarID = c.StarterID 
            WHERE c.ConversationID IN (SELECT r.ConversationID FROM Enrollments AS e JOIN Recipients AS r ON r.CourseID = e.CourseID WHERE e.StarID = :UserID AND e.CourseID=:CourseID AND r.Type='Course') 
            OR c.ConversationID IN (SELECT r.ConversationID FROM Recipients AS r JOIN Conversations AS con ON con.ConversationID = r.ConversationID WHERE con.StarterID=:UserID AND r.CourseID = :CourseID)
            OR c.ConversationID IN (SELECT r.ConversationID FROM Courses AS c JOIN Recipients AS r ON r.CourseID = c.OrgID WHERE c.InstructorID= :UserID AND c.OrgID=:CourseID AND r.Type='Instructor')
            ORDER BY LastMessageDate DESC", array("UserID" => $this->user->getUserID(), "CourseID" => $orgID)); 
        
        } 
        else 
        {
            $messages = $this->db->query(
            "SELECT c.StarterID, c.ConversationID, (SELECT Date FROM Messages AS m WHERE m.ConversationID=c.ConversationID ORDER BY MessageID DESC LIMIT 1) AS LastMessageDate, c.Title, u.StarID, u.Firstname, u.Lastname, u.Role 
            FROM Conversations AS c
            JOIN Users AS u ON u.StarID = c.StarterID 
            WHERE " . $addition . " (c.ConversationID IN (SELECT ConversationID FROM Recipients WHERE UserID=:UserID) OR c.StarterID =:UserID 
            OR c.ConversationID IN (SELECT r.ConversationID FROM Enrollments AS e JOIN Recipients AS r ON r.CourseID = e.CourseID WHERE e.StarID = :UserID AND r.Type='Course')
            OR c.ConversationID IN (SELECT r.ConversationID FROM Courses AS c JOIN Recipients AS r ON r.CourseID = c.OrgID WHERE c.InstructorID = :UserID AND r.Type='Instructor')
            OR ('" . $this->user->getAttribute("Role") . "' = 'student' AND 'allStudents' IN (SELECT UserID FROM Recipients AS r WHERE r.ConversationID=c.ConversationID)) 
            OR (('" . $this->user->getAttribute("Role") . "' = 'teacher' OR '" . $this->user->getAttribute("Role") . "'='admin') AND 'allStaff' IN (SELECT UserID FROM Recipients AS r WHERE r.ConversationID=c.ConversationID)))
            ORDER BY LastMessageDate DESC", array("UserID" => $this->user->getUserID())); 
        }
        
        foreach($messages AS $message) {
            
            $latest = $this->db->query("SELECT Content FROM Messages WHERE ConversationID =:ConversationID ORDER BY MessageID DESC LIMIT 1", array("ConversationID" => $message->ConversationID), "SINGLE");
            $message->Latest = $latest->Content;
            
            $read = $this->db->query("SELECT UserID FROM Views WHERE ConversationID=:ConversationID AND UserID=:UserID AND Date >= :Date", array("UserID" => $this->user->getUserID(), "ConversationID" => $message->ConversationID, "Date" => $message->LastMessageDate));
            $message->Read = (count($read) > 0) ? true : false;
            
            $message->viewAccess = false;

            if($message->StarterID == $this->user->getUserID()) {
            
                $message->Firstname = "Me";
                $message->Lastname = "";

                if($this->user->getAttribute("Role") == "admin" || $this->user->getAttribute("Role") == "teacher") {
                    $message->viewAccess = true;
                } 
            
            }

            $message->Recipients = $this->db->query("SELECT r.UserID,r.CourseID,r.Type,u.Firstname,u.Lastname,c.Name AS CourseName FROM Recipients AS r LEFT JOIN Users AS u ON u.StarID=r.UserID LEFT JOIN Courses AS c ON c.OrgID=r.CourseID WHERE r.ConversationID=:ConversationID", array("ConversationID" => $message->ConversationID));
            
            $message->isFavorite = (count($this->db->query("SELECT ConversationID FROM Favorites WHERE ConversationID=:ConversationID AND UserID=:UserID", array("ConversationID" => $message->ConversationID, "UserID" => $this->user->getUserID()))) > 0);
        }
        
        return $messages;
        
    }
    
    
    public function view($conversationID, $order = "DESC") {
        
        $date = date("Y-m-d H:i:s");
        
        $order = (strtolower($order) == "asc") ? "ASC" : "DESC";
        
        $conversation = $this->db->query("SELECT ConversationID, StarterID, Title FROM Conversations WHERE ConversationID=:ConversationID", array("ConversationID" => $conversationID), "SINGLE");
        
        $users = $this->getRecipients($conversation);
        
         //Determine if accessible
         if(!$this->isRecipient($conversation, $users, $this->user->getUserID())) {
             
            Flasher::set("errorMessage", array("class" => "failure", "message" => "You are not apart of this conversation"));
            return;
          
        }
        if($this->user->getImpersonateID()) {
            
            Flasher::set("errorMessage", array("class" => "failure", "message" => "You are not allowed to view messages during impersonation"));
            return;

        }
        
        $conversation->Recipients = $this->db->query("SELECT r.UserID,r.CourseID,r.Type,u.Firstname,u.Lastname,c.Name AS CourseName FROM Recipients AS r LEFT JOIN Users AS u ON u.StarID=r.UserID LEFT JOIN Courses AS c ON c.OrgID=r.CourseID WHERE r.ConversationID=:ConversationID", array("ConversationID" => $conversationID));
      
        $this->response->setTitle("View Conversation: " . $conversation->Title);
        
        $messages = $this->db->query("SELECT m.MessageID, u.StarID, u.Firstname, u.Lastname, m.Content, m.Date FROM Messages AS m JOIN Users AS u on u.StarID = m.SenderID WHERE m.ConversationID=:ConversationID ORDER BY m.MessageID " . $order, array("ConversationID" => $conversationID));
        
        //Mark conversation as viewed
        $markViewed = $this->db->query("INSERT INTO Views (ConversationID, UserID, Date) VALUES (:ConversationID, :UserID, :Date)", array("ConversationID" => $conversationID, "UserID" => $this->user->getUserID(), "Date" => $date));
        
        $this->unread = $this->determineUnreadMessages();
        $this->response->push("unreadMessages", $this->unread);
        $this->view->push("unreadMessages", $this->unread);
        
        $this->view->push("conversation", $conversation);
        $this->view->push("order", $order);
        $this->view->push("messages", $messages);
        $this->view->push("userID", $this->user->getUserID());
    
    }
    
    
    public function reply($conversationID) {
        
        $errors = array();
        
        $conversation = $this->db->query("SELECT ConversationID,Title FROM Conversations WHERE ConversationID=:ConversationID", array("ConversationID" => $conversationID), "SINGLE");
        
        if(empty($_POST['message']) || !$conversation) {
        
            header( 'Location: ' . SITE_PATH . '/messages/view/' . $conversationID ); 
            return "Message not posted";
        }
        
        $date = date("Y-m-d H:i:s");

        $message = $this->purifier->purify($_POST['message']);
        
        $newMessage = $this->db->query("INSERT INTO Messages (ConversationID, SenderID, Content, Date) VALUES (:ConversationID, :UserID, :Content, :Date)",
                                array("ConversationID" => $conversationID, "UserID" => $this->user->getUserID(), "Content" => $message, "Date" => $date));
                                
        //Update last post date

        header( 'Location: ' . SITE_PATH . '/messages/view/' . $conversationID ); 
   
        //Set successful reply
        Flasher::set("message", array("class" => "success", "message" => "Message successfully sent"));
        
        
        //Send an email to all recipients
        $template = new EmailTemplate("newMessage");
        $template->push("poster", $this->user->getAttribute("Firstname") . " " . $this->user->getAttribute("Lastname"));
        $template->push("conversationID", $conversationID);
        $template->push("conversationTitle", $conversation->Title);
        
        $users = $this->getRecipients($conversation);
        
        $mail = $this->mailer->create();
        $mail->From = "info@starpro.me";
        $mail->FromName = "info@starpro.me";
        
        //Send to all conversation users except the replier

        $first = true;
        foreach($users AS $userID => $user) {
            if($user['StarID'] != $this->user->getUserID() && !empty($user['Email'])) {
                if($first) { 
                    $mail->AddAddress($user['Email']);
                    $first = false;
                    continue;
                }
                $mail->AddCC($user['Email']);
            }
        }
        
        $mail->AddReplyTo("info@starpro.me", "Information");
        $mail->Subject = "New Message from " . $this->user->getAttribute("Firstname") . " " . $this->user->getAttribute("Lastname") . " - eTECH Portal";
        $mail->Body = $template->render();
        
        if(!$first) {
            $mail->send();
        }    
        
        return "Message Posted"; 
    
    }
    
    public function newMessage($recipient = null) {
    
        $this->response->setTitle("New Message");
   
        $contacts = $this->getContacts();
        
        if($recipient) {
            $recipient = urldecode($recipient);
            $recipient = explode(":", $recipient);

            $this->view->push("recipientID", $recipient[0]);
            $this->view->push("recipientDisplay", $recipient[1]);
        }
        
        $this->view->push("contacts", $contacts); 
        
        $this->view->push("user", $this->user); 

    }
    
    public function createConversation() {
        
        $this->response->setJSON();
        
        $recipients = $_POST['recipients'];
        
        $subject = strip_tags($_POST['subject']);
        
        $message = $this->purifier->purify($_POST['message']);
        
        $date = date("Y-m-d H:i:s");
        
        //Error conditions
        $errors = array();
        
        $contacts = $this->getContacts();

        if(empty($recipients) || count($recipients) == 0) {
        
            $errors['recipients'] = "Please specify at least one recipient";
        
        } else {
        
            foreach($recipients AS $recipient) {
                
                if(!array_key_exists($recipient, $contacts)) {
                
                    $errors['recipients'] = "The recipient, " . $recipient . " is not valid or you do not have permission to contact this recipient";
                
                }
            
            }
        }
        
        if(empty($subject)) {
        
           $errors['subject'] = "Please enter a subject";  
        
        }
        
        if(empty($message)) {
        
            $errors['message'] = "Please enter a message"; 
        
        }
        
        if(count($errors) > 0) {
        
            return json_encode(array("errors" => true, "errorMessages" => $errors));
        
        }
        
        $newConversation = $this->db->query("INSERT INTO Conversations (StarterID, Title, Date) VALUES (:UserID, :Subject, :Date)", array("UserID" => $this->user->getUserID(), "Subject" => $subject, "Date" => $date));
        
        $newMessage = $this->db->query("INSERT INTO Messages (ConversationID, SenderID, Content, Date) VALUES (:ConversationID, :UserID, :Content, :Date)", 
                                        array("ConversationID" => $newConversation, "UserID" => $this->user->getUserID(), "Content" => $message, "Date" => $date));
                                        
       
         //Create recipients for both courses and single users
        foreach($recipients AS $recipient) {
            
            if(strpos($recipient, "course") !== false) {
            
                $courseID = str_replace("course-", "", $recipient);
                
                $newRecipient = $this->db->insert("Recipients", array("ConversationID" => $newConversation, "CourseID" => $courseID, "Type" => "Course"));
          
                continue;
            }
            if(strpos($recipient, "instructor") !== false) {
            
                $courseID = str_replace("instructor-", "", $recipient);
                
                $newRecipient = $this->db->insert("Recipients", array("ConversationID" => $newConversation, "CourseID" => $courseID, "Type" => "Instructor"));
                
                continue;
            }

            $user = $this->db->query("SELECT StarID FROM Users WHERE UserID=:UserID", array("UserID" => $recipient), "SINGLE");

            if($user) {
                $newRecipient = $this->db->insert("Recipients", array("ConversationID" => $newConversation, "UserID" => $user->StarID, "Type" => "User"));
            }
        
        }
        
        //Mark conversation as read
        $view = $this->db->query("INSERT INTO Views (ConversationID, UserID, Date) VALUES (:ConversationID, :UserID, :Date)", array("ConversationID" => $newConversation, "UserID" => $this->user->getUserID(), "Date" => $date));
        
        //Flash Message
        Flasher::set("message", array("class" => "success", "message" => "Message successfully sent"));
        
        //Send an email to all recipients
        $template = new EmailTemplate("newMessage");
        $template->push("poster", $this->user->getAttribute("Firstname") . " " . $this->user->getAttribute("Lastname"));
        $template->push("conversationID", $newConversation);
        $template->push("conversationTitle", $subject);
        
        $conversation = new \STDClass();
        $conversation->ConversationID = $newConversation;
        $conversation->Title = $subject;
        
        $users = $this->getRecipients($conversation);
        
        $mail = $this->mailer->create();
        $mail->From = "info@starpro.me";
        $mail->FromName = "info@starpro.me";

        //Send to all conversation users except the replier

        $first = true;
        foreach($users AS $userID => $user) {
            if($user['StarID'] != $this->user->getUserID() && !empty($user['Email'])) {
                if($first) {
                    $mail->AddAddress($user['Email']);
                    $first = false;
                    continue;
                }
                $mail->AddCC($user['Email']);
            }
        }
        
        $mail->AddReplyTo("info@starpro.me", "Information");
        $mail->Subject = "New Conversation from " . $this->user->getAttribute("Firstname") . " " . $this->user->getAttribute("Lastname") . " - eTECH Portal";
        $mail->Body = $template->render();
        
        if(!$first) {
            $mail->send();
        }       
        
        return json_encode(array("message" => "Conversation successfully created"));
    
    }
    
    public function getContacts($search = "", $orgID = 0) {
 
        $contacts = array();

        //Add admins
        $users = $this->db->query("SELECT UserID, Firstname, Lastname, Role FROM Users WHERE (UserID LIKE :search OR Firstname LIKE :search OR Lastname LIKE :search OR CONCAT(Firstname, ' ', Lastname) LIKE :search) AND Role = 'admin'",
                              array("search" => "%" . $search . "%") );
        
        foreach($users AS $user) {
            
            $contacts[$user->UserID] = array("userID" => $user->UserID, "role" => $user->Role, "display" => $user->Firstname . " " . $user->Lastname, "suggestion" => $user->Firstname . " " . $user->Lastname);
        
        }

        //Add yourself
        $user = $this->db->query("SELECT UserID, Firstname, Lastname, Role FROM Users WHERE (UserID LIKE :search OR Firstname LIKE :search OR Lastname LIKE :search OR CONCAT(Firstname, ' ', Lastname) LIKE :search) AND StarID = :StarID",
                              array("StarID" => $this->user->getUserID(), "search" => "%" . $search . "%"), "SINGLE" );
        if($user) {
           $contacts[$user->UserID] = array("userID" => $user->UserID, "role" => $user->Role, "display" => $user->Firstname . " " . $user->Lastname, "suggestion" => $user->Firstname . " " . $user->Lastname);
        }

        if($this->user->getAttribute("Role") == "admin") {

            $staff = $this->db->query("SELECT u.UserID,u.Firstname,u.Lastname,u.Role FROM Users AS u WHERE (u.UserID LIKE :search OR u.Firstname LIKE :search OR u.Lastname LIKE :search)", array("search" => "%" . $search . "%"));
            
            foreach($staff AS $user) {
            
                $contacts[$user->UserID] = array("userID" => $user->UserID, "role" => $user->Role, "display" => $user->Firstname . " " . $user->Lastname, "suggestion" => $user->Firstname . " " . $user->Lastname);
            
            }

            $classes = $this->db->query("SELECT OrgID, Name FROM Courses WHERE (Name LIKE :search OR OrgID LIKE :search)", array("search" => "%" . $search . "%"));

            foreach($classes AS $class) {
            
                $contacts["course-".$class->OrgID] = array("display" => "Students in " . $class->Name, "suggestion" => $class->Name . " (" . $class->OrgID . ")");
                $contacts['instructor-' . $class->OrgID] = array("display" => "Instructor of " . $class->Name, "suggestion" => "Instructor of " . $class->Name . "(" . $class->OrgID . ")"); 
            }

            $contacts["allStaff"] = array("display" => "All Staff", "suggestion"=> "All eTECH Staff");
            $contacts["allStudents"] = array("display" => "All Students", "suggestion"=> "All eTECH Students");

        }

        if($this->user->getAttribute("Role") == "teacher") {

            $classes = $this->db->query("SELECT OrgID, Name FROM Courses WHERE InstructorID=:UserID", array("UserID" => $this->user->getUserID()));

            foreach($classes AS $class) {
                
                if (empty($search) || stripos($class->Name, $search) !== false) {
                    $contacts["course-".$class->OrgID] = array("display" => "Students in " . $class->Name, "suggestion" => $class->Name . " (" . $class->OrgID . ")");
                }
            
                $enrollments = $this->db->query("SELECT u.UserID,u.Firstname,u.Lastname,u.Role FROM Enrollments AS e JOIN Users AS u ON u.StarID = e.StarID WHERE e.CourseID=:CourseID AND (u.UserID LIKE :search OR u.Firstname LIKE :search OR u.Lastname LIKE :search)", array("CourseID" => $class->OrgID, "search" => "%" . $search . "%"));
         
                foreach($enrollments AS $enrollment) {

                    $contacts[$enrollment->UserID] = array("userID" => $enrollment->UserID,  "role" => $enrollment->Role, "display" => $enrollment->Firstname . " " . $enrollment->Lastname, "suggestion" => $enrollment->Firstname . " " . $enrollment->Lastname);
            
                }

            }

        }

        if($this->user->getAttribute("Role") == "student") {
            
            $enrolledCourses = $this->db->query("SELECT c.CourseID,c.OrgID,c.Name FROM Enrollments AS e JOIN Courses AS c ON c.OrgID = e.CourseID WHERE StarID=:UserID", array("UserID" => $this->user->getUserID()));
            
            foreach($enrolledCourses AS $course) {
                if (empty($search) || stripos($course->Name, $search) !== false) {
                    $contacts['instructor-' . $course->CourseID] = array("display" => "Instructor of " . $course->Name, "suggestion" => "Instructor of " . $course->Name . "(" . $course->CourseID . ")"); 
                }
                $enrolled = $this->db->query("SELECT u.UserID,u.Firstname,u.Lastname,u.Role FROM Enrollments AS e JOIN Users AS u ON u.StarID = e.StarID WHERE (u.UserID LIKE :search OR u.Firstname LIKE :search OR u.Lastname LIKE :search) AND e.CourseID=:CourseID", array("search" => "%" . $search . "%", "CourseID" => $course->OrgID));
                
                foreach($enrolled AS $user) {
                    $contacts[$user->UserID] = array("userID" => $user->UserID, "role" => $user->Role, "display" => $user->Firstname . " " . $user->Lastname, "suggestion" => $user->Firstname . " " . $user->Lastname . ($user->Role == "teacher" ? " (instructor)" : ""));            
                }
            } 
            
        }

        if($orgID !=0) {

            $userIDs = [];
            $enrollments = $this->db->query("SELECT u.UserID FROM Enrollments AS e JOIN Users AS u ON u.StarID = e.StarID WHERE e.CourseID=:orgID", array("orgID" => $orgID));
            foreach($enrollments AS $enrollment) {
                $userIDs[] = $enrollment->UserID;
            }
            if(!in_array($this->user->getAttribute("UserID"), $userIDs)) {
                $contacts = [];
            }

            foreach($contacts AS $key=>$contact) {

                if(isset($contact["userID"]) && in_array($contact['userID'], $userIDs)) {
                  
                    continue;

                }

                unset($contacts[$key]);
            }

        }


        return $contacts;
        
    }
    
    protected function getRecipients($conversation) {
        
        $conversationID = $conversation->ConversationID;
        
        $users = array();
        
        $processedAdmins = false;
        
        $enrollments = $this->db->query("SELECT 
            r.ConversationID,r.CourseID,r.UserID,
            u.Firstname,u.Lastname,u.Email, 
            e.StarID, s.Firstname AS CourseFirst, s.Lastname AS CourseLast, s.Email AS CourseEmail, 
            t.StarID AS StarterID, t.Firstname AS StarterFirstname, t.Lastname AS StarterLastname, t.Email AS StarterEmail, inst.StarID AS InstructorID, inst.Firstname AS InstFirstname, inst.Lastname AS InstLastname, inst.Email AS InstEmail 
            FROM `Recipients` AS r 
            LEFT JOIN Enrollments AS e ON e.CourseID = r.CourseID AND r.Type='Course'
            LEFT JOIN Courses AS courses ON courses.OrgID = r.CourseID 
            LEFT JOIN Users AS inst ON inst.StarID = courses.InstructorID 
            LEFT JOIN Users AS u ON u.StarID = r.UserID
            LEFT JOIN Users AS s ON s.StarID = e.StarID
            LEFT JOIN Conversations AS c ON c.ConversationID = r.ConversationID
            LEFT JOIN Users AS t ON t.StarID = c.StarterID
            WHERE r.ConversationID=:ConversationID", array("ConversationID" => $conversationID)
        );
        
        foreach($enrollments AS $enrollment) {
            
            //Add all students to the list
            if($enrollment->UserID == "allStudents") {
                
                $moreUsers = $this->db->query("SELECT StarID,Firstname,Lastname,Email FROM Users WHERE Role='student'");
                
                foreach($moreUsers AS $more) {
                    $users[$more->StarID] = array("StarID" => $more->StarID, "Firstname" => $more->Firstname, "Lastname" => $more->Lastname, "Email" => $more->Email);
                }
                
                continue;
                
            }
            
            //Add all staff to the list
            if($enrollment->UserID == "allStaff") {
                $moreUsers = $this->db->query("SELECT StarID,Firstname,Lastname,Email FROM Users WHERE Role='teacher' OR Role='admin'");
                
                foreach($moreUsers AS $more) {
                    $users[$more->StarID] = array("StarID" => $more->StarID, "Firstname" => $more->Firstname, "Lastname" => $more->Lastname, "Email" => $more->Email);
                }
                
                continue;
            }

               
            //Else add the enrolled users, recipients and the conversation starter
            if(!empty($enrollment->UserID)) {
                $users[$enrollment->UserID] = array("StarID" => $enrollment->UserID, "Firstname" => $enrollment->Firstname, "Lastname" => $enrollment->Lastname, "Email" => $enrollment->Email);
            }
            if(!empty($enrollment->StarID)) {
                $users[$enrollment->StarID] = array("StarID" => $enrollment->StarID, "Firstname" => $enrollment->CourseFirst, "Lastname" => $enrollment->CourseLast, "Email" => $enrollment->CourseEmail);
            }
            if(!empty($enrollment->StarterID)) {
                $users[$enrollment->StarterID] = array("StarID" => $enrollment->StarterID, "Firstname" => $enrollment->StarterFirstname, "Lastname" => $enrollment->StarterLastname, "Email" => $enrollment->StarterEmail);
            }

            if(!empty($enrollment->InstructorID)) {
                $users[$enrollment->InstructorID] = array("StarID" => $enrollment->InstructorID, "Firstname" => $enrollment->InstFirstname, "Lastname" => $enrollment->InstLastname, "Email" => $enrollment->InstEmail);
            }
            
        }
        
        return $users;
    
    }
    
    protected function isRecipient($conversation, $users, $user) {
        
        if($user == $conversation->StarterID) {
            return true;
        }
        
        foreach($users AS $userID => $userData) {
        
            if($userData['StarID'] == $user) {
                return true;
            }
        
        }
        
        return false;
    }
    
    /*
        Determine who viewed - or didn't view - a particular conversation
    */
    public function views($conversationID, $unread = "yes") {

  
        $conversation = $this->db->query("SELECT ConversationID, StarterID, Title FROM Conversations WHERE ConversationID=:ConversationID", array("ConversationID" => $conversationID), "SINGLE");
        
        if($this->user->getUserID() != $conversation->StarterID || ($this->user->getAttribute("Role") != "teacher" && $this->user->getAttribute("Role") != "admin")) {
            
            Flasher::set("message", array("class" => "failure", "message" => "You do not have permission to view this page."));
            return;  
            
        }

        if(!$conversation) {
            
            Flasher::set("message", array("class" => "failure", "message" => "Conversation not found"));
            return;
        }
  
        $users = $this->getRecipients($conversation);
        
         //Determine if accessible
         if(!$this->isRecipient($conversation, $users, $this->user->getUserID())) {
             
            Flasher::set("message", array("class" => "failure", "message" => "You are not apart of this conversation"));
            return;
          
        }
 
        foreach($users AS $id=>$user) {
    
            $userDidView = $this->db->query("SELECT Date FROM Views WHERE UserID=:UserID AND ConversationID=:ConversationID", array("UserID" => $user['StarID'], "ConversationID" => $conversationID));
            
            //If the user has not viewed and the view is set to read messages, discard
            if(count($userDidView) == 0 && $unread != "no") {
                
                unset($users[$id]);
                
            }
            
            //If the user has viewed and the conversation is set to unread messages, discard
            else if(count($userDidView) != 0 && $unread == "no") {
                unset($users[$id]);
                
            }

            if(empty($user['Firstname'])) {
                unset($users[$id]);
            }
        
        }

        if($unread != "no") {
            $this->view->push("message", "Recipients That Read <a href='" . SITE_PATH . "/messages/view/" . $conversationID . "'>\"" . $conversation->Title . "\"</a>");
            $this->view->push("unread", false);
        } else {
            $this->view->push("message", "Recipients That Have Not Read <a href='" . SITE_PATH . "/messages/view/" . $conversationID . "'>\"" . $conversation->Title . "\"</a>");
            $this->view->push("unread", true);
        }
        $this->view->push("conversationID", $conversationID);
        $this->view->push("users", $users);
        
     
    }
    
    
    public function contacts() {
        
        $search = isset($_GET['search']) ? $_GET['search'] : null;
        $orgID = isset($_GET['courseID']) ? $_GET['courseID'] : null;
        
        $this->response->setJSON();
        
        $contacts = $this->getContacts($search, $orgID);
        
        return json_encode($contacts);
    
    }
    
    /*
        Recipient Search Completion, allows users to search for contacts
    */
    public function search() {
        
        $search = $_GET['search'];
        
        $this->response->setJSON();
        
        $contacts = $this->getContacts($search);
        
        $responseObject = new \STDClass();
        
        foreach($contacts AS $id=>$values) {
        
            $responseObject->$id = new \STDClass();
            $responseObject->$id->id = $id;
            $responseObject->$id->key = $values['display'];
            $responseObject->$id->suggestion = $values['suggestion'];
            $responseObject->$id->suggestable = true;
        
        }
        
        return json_encode($responseObject);
        
    }
    

    public function favorite($conversationID) {
        
        $this->response->setJSON();
        
        $findFav = $this->db->query("SELECT FavoriteID FROM Favorites WHERE ConversationID=:ConversationID AND UserID=:UserID", array("ConversationID" => $conversationID, "UserID" => $this->user->getUserID()));

        if(count($findFav) != 0) {
        
            return json_encode(array("message" => "failure"));
        
        }
        
        $this->db->query("INSERT INTO Favorites (ConversationID, UserID) VALUES (:ConversationID, :UserID)", array("ConversationID" => $conversationID, "UserID" => $this->user->getUserID()));
        
        return json_encode(array("message" => "success"));
    }
    
     public function unfavorite($conversationID) {

        $this->response->setJSON();
        
        $this->db->query("DELETE FROM Favorites WHERE ConversationID=:ConversationID AND UserID=:UserID LIMIT 1", array("ConversationID" => $conversationID, "UserID" => $this->user->getUserID()));
        
        return json_encode(array("message" => "success"));
    }

}

?>