<?php
namespace Etech\Models;
use Etech\Classes\Flasher AS Flasher;

class Home extends Model {

    public function main() {
    
        $this->response->setTitle("Home");
        $this->view->push("authenticated", $this->user->isAuthenticated());
    }
    
    public function courses() {
        
        $this->response->setTitle("View your Courses");
        $this->view->push("userID", $this->user->getUserID());
    
    }
    
    public function doAuth() {
        if($this->user->isAuthenticated()) {
            
            header("location: " . SITE_PATH . "/home/");
 
        }
    }
    
    public function logout() {
    
        $this->response->setTitle("Log out");
        
        unset($_COOKIE['impersonateID']);
        unset($_COOKIE['SimpleSAMLAuthToken']);
        
        $as = new \SimpleSAML_Auth_Simple('default-sp');
        $as->logout(SITE_PATH . '/');
    
    }
    

    /*
    
        Returns the authenticated user's campuses they are enrolled in

    */

    protected function getEnrolledCampuses() {
        
        $userID = $this->user->getUserID();

        return $this->db->query("SELECT DISTINCT c.Campus AS Code, s.Campus, s.Emblem, s.Location, s.FullLink AS src
        FROM  `Enrollments` AS e
        JOIN Courses AS c ON c.OrgID = e.CourseID
        JOIN Campuses AS s ON s.Code = c.Campus
        WHERE e.StarID = :UserID", array("UserID" => $userID));
           
    
    }

    /*
    
        Returns the authenticated user's enrollments

    */
    function getEnrollments() {

        $userID = $this->user->getUserID();

        $this->response->setJSON();
      
        if(!$this->user->getImpersonateID()) {
            $this->logger->logEvent($userID, "Viewed Courses");
        }
 
        $courses = $this->db->query("SELECT e.CourseID 
        FROM Enrollments AS e 
        JOIN Courses AS c ON c.OrgID = e.CourseID
        LEFT JOIN Campuses AS ca ON ca.Code = c.Campus
        WHERE e.StarID=:UserID 
        ORDER BY ca.Campus ASC, c.YearTerm DESC,c.Name ASC", array("UserID" => $userID));
        
        return json_encode(array("courses" => $courses));
        
    }
    
  
    /*
        Given a userID, get the campuses this user is enrolled in 
    */
    public function getData() {
        
        $this->response->setJSON();

        $campuses = $this->getEnrolledCampuses();
        
        $courses = $this->db->query("SELECT c.CourseID, c.OrgID, c.Name, t.Term AS YearTerm, t.Code AS Term, c.Campus, c.StartDate, c.EndDate, l.Name AS LibraryName, l.Image AS LibraryImage, l.Link as LibraryLink, u.Firstname, u.Lastname 
                FROM Courses AS c 
                LEFT JOIN Users AS u ON u.StarID = c.InstructorID 
                LEFT JOIN Terms AS t ON t.Code = c.YearTerm
                LEFT JOIN Libraries AS l ON l.Library = c.Library
                LEFT JOIN Campuses AS ca ON ca.Code = c.Campus
                ORDER BY ca.Campus ASC, c.YearTerm DESC,c.Name ASC");
     
        foreach($courses AS $course) {

            $course->StartDate = ($course->StartDate == "0000-00-00") ? null : date("F j, Y", strtotime($course->StartDate));
            $course->EndDate = ($course->EndDate == "0000-00-00") ? null : date("F j, Y", strtotime($course->EndDate));
        }
     
        return json_encode(array("campuses" => $campuses, "courses" => $courses));
    }

    public function getCourseData($courseID) {
        $this->response->setJSON();

        $course = $this->db->query("SELECT Campus,OrgID FROM Courses WHERE CourseID=:CourseID", array("CourseID" => $courseID), "SINGLE");

        $campus = $this->db->query("SELECT Code, Emblem, Location, Campus, FullLink AS src FROM Campuses WHERE Active = 1 AND Code=:Code", array("Code" => $course->Campus));


        return json_encode(array("campus" => $campus, "orgID" => $course->OrgID));
    }
    

    /*
        Returns true for legacy widget
    */
    public function isSynced($courseID)  {

        $this->response->setJSON();

        return $_GET['callback'] . "(" . json_encode( array( "synced" => true ) ) . ")";

    }

    public function searchCourses() {

        $search = $_GET['search'];
        
        $this->response->setJSON();
        
        $courses = $this->db->query("SELECT CourseID,OrgID,Name FROM Courses WHERE Name LIKE :search AND Exclude = 0 AND YearTerm=:YearTerm", array("YearTerm" => YEARTERM, "search" => "%" . $search . "%"));
        
        $responseObject = new \STDClass();
        
        foreach($courses AS $course) {
            
            $responseObject->{$course->CourseID} = (object) array("id" => $course->CourseID, "key" => $course->Name, "suggestion" => $course->Name, "suggestable" => true);
        
        }
        
        return json_encode($responseObject);

    }


    public function forwarding() {
        $this->response->setTitle("Instructions for Forwarding Emails");
    }

    public function resources($courseID) {

        $course = $this->db->query("SELECT CourseID, Name FROM Courses WHERE CourseID=:Course", array("Course" => $courseID), "SINGLE");

        $resources = $this->db->query("SELECT Name, Edition, Author, ISBN13, ISBN10, Optional, Image FROM Books WHERE CourseID=:Course", array("Course" => $courseID));

        $this->view->push("course", $course);
        $this->view->push("resources", $resources);

    }

    
}

?>