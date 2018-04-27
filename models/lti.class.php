<?php
namespace Etech\Models;
use Etech\Classes\Flasher AS Flasher;

class LTI extends Model {


    public function widget() {

        $this->response->setTemplate("widgettemplate.php");

        $dbconnector = \LTI_Data_Connector::getDataConnector('', $this->db, 'PDO');

        $tool = new \Etech\Classes\PortalToolProvider($dbconnector);
        $tool->execute();

        if($tool->isOK) {
            
            $starID = $_POST['ext_d2l_username'];
            $firstname = $_POST['lis_person_name_given'];
            $lastname = $_POST['lis_person_name_family'];
            $email = $_POST['lis_person_contact_email_primary'];

            $courseID = intval($_POST['context_id']);

            $this->logger->logEvent($starID, "LTI Success", "StarID: " . $starID . " Course: " . $courseID . " " . $_POST['context_title']);

            $target = "instructor";

            $isTeacher = (strpos($_POST['roles'],'Instructor') !== false || strpos($_POST['roles'],'Faculty') !== false); 
      
            $courseData = $this->db->query("SELECT Name FROM Courses WHERE OrgID=:CourseID", array("CourseID" => $courseID), "SINGLE");
            if(!$courseData) {  

                $campus = preg_match("/https?:\/\/(.+)?\.learn.minnstate.edu(.+)?/i", $_POST['lis_outcome_service_url'], $matches);

                $courseData = array("OrgID" => $courseID, "Name" => $_POST['context_title'], "Campus" => $matches[1]);
                if($isTeacher && isStarID($starID)) {
                    $courseData['InstructorID'] = $starID;
                }

                $course = $this->db->insert("Courses", $courseData);
                $courseData = (object) $courseData;

                $this->logger->logEvent($starID, "Course Created", "User:".$starID . " Course: " . $courseID . " " . $_POST['context_title']);

            }

            $target = $isTeacher ? "students" : "instructor";

            $this->view->push("target", $target);

            //Only deal with starID users
            if(isStarID($starID)) {

                $enrollCheck = $this->db->query("SELECT CourseID FROM Enrollments WHERE StarID=:StarID AND CourseID=:CourseID", array("StarID" => $starID, "CourseID" => $courseID), "SINGLE");

                if(!$enrollCheck) {

                    $newEnrollment = $this->db->insert("Enrollments", array("StarID" => $starID, "CourseID" => $courseID, "Source" => "Widget", "Time" => getDateTime()));

                    $this->logger->logEvent($starID, "Enrollment", "Course: " . $courseID . " " . $_POST['context_title']);
                }

                //Prompt new user to create an account
                $user = $this->db->query("SELECT Firstname, Lastname, HomeCampus, Email, Role, Avatar FROM Users WHERE StarID=:UserID LIMIT 1", array("UserID" => $starID), "SINGLE");
           
                if(!$user) {

                    $this->view->setFile("lti", "newUser");

                    return;

                }

    
            }

            $messageTarget = "";
            if($target == "students") {

                $messageTarget = "course-".$courseID . ":Students in " . $courseData->Name;

            } else {

                $messageTarget = "instructor-".$courseID . ":Instructor of " . $courseData->Name;

            }

            $this->view->push("messageTarget", $messageTarget);
   
            $this->view->push("name", $firstname);
         
            $this->view->push("newMessages", $this->app->get("\\Etech\\Models\\messages")->determineUnreadMessages($starID));

        } else {

            $this->logger->logEvent($starID, "LTI Attempt Failure", "Course: " . $courseID . " User: " . $starID);
            Flasher::set("errorMessage", array("class" => "failure", "message" => "An error occurred viewing this widget, please try again later."));
            
        }

    }


}