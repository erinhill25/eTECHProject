<?php
namespace Etech\Models;
use Etech\Classes\Flasher AS Flasher;

class Admin extends Model {
    
    protected $usersPerPage = 25;

    public function __construct($db, $response, $user, $mailer, $logger, $app) {
        parent::__construct($db, $response, $user, $mailer, $logger, $app);
 
        $impersonateID = $this->user->getImpersonateID();
        
        if(!empty($impersonateID)) {
            
            $this->response->push("impersonateID", $impersonateID);
            
        }
        
    }
    
    public function run() {
        $this->checkPermission();
    }
    
    public function checkPermission() {
        
       if($this->user->getAttribute("Role") != "admin") {
            header("location: " . SITE_PATH . "/home/");
       }
        
    }
    
    public function main() {
 
        $this->response->setTitle("Administration");
        
    }
    
    public function users() {

        $this->response->setTitle("Administration - View Users");
    
    }

    public function logs() {

        $this->response->setTitle("Administration - View Logs");
    
    }

    public function courses() {
        $this->response->setTitle("Administration - View Courses");


        $courses = $this->db->query("SELECT c.OrgID, c.Name, c.StartDate, c.EndDate, t.Firstname, t.Lastname, p.Campus, m.Term, (SELECT Date FROM Hoovers WHERE CourseID=c.OrgID ORDER BY Date DESC LIMIT 1) AS LastEnrollment,
            (SELECT count(*) FROM Enrollments WHERE CourseID=c.OrgID) AS Enrollments
            FROM Courses AS c 
            LEFT JOIN Users AS t ON t.StarID=c.InstructorID 
            LEFT JOIN Campuses AS p ON p.Code=c.Campus
            LEFT JOIN Terms AS m ON m.Code=c.YearTerm
            WHERE c.Exclude != 1
            ORDER BY c.CourseID DESC
            ");

        $this->view->push("courses", $courses);

    }

    public function getEnrollments($courseID) {

        $this->response->setJSON();

        $enrollments = $this->db->query("SELECT u.Firstname, u.Lastname, e.StarID,e.Time FROM Enrollments AS e LEFT JOIN Users AS u ON u.StarID = e.StarID WHERE e.CourseID=:CourseID", array("CourseID" => $courseID));

        foreach($enrollments AS $enrollment) {
            $enrollment->Time = smartDate(strtotime($enrollment->Time));
        }

        return json_encode(array("enrollments" => $enrollments));

    }
    
    public function searchUsers($search = null, $offset=0) {
        
        $search = urldecode($search);
        $this->response->setJSON();

      $queryAddition = " ORDER BY DateRegistered DESC";
        if(isset($offset)) { 
            $queryAddition .= " LIMIT " . $this->usersPerPage . " OFFSET " . $offset;
        }
     
        $users = $this->db->query(
                "SELECT Firstname, Lastname, StarID, Role, LastLogin, DateRegistered, Email 
                FROM Users 
                WHERE Firstname LIKE :search OR Lastname LIKE :search OR StarID LIKE :search OR Role LIKE :search OR Email LIKE :search OR CONCAT(Firstname, ' ', Lastname) LIKE :search" . $queryAddition,
                array("search" => "%" . $search . "%")
        );

        foreach($users AS $user) {
            $user->LastLogin = !empty($user->LastLogin) ? smartDate(strtotime($user->LastLogin)) : "-";
            $user->DateRegistered = !empty($user->DateRegistered) ? smartDate(strtotime($user->DateRegistered)) : "-";
        }

        $newOffset = $offset + count($users);

                
        return json_encode(array("users" => $users, "offset" => $newOffset));        
        
    }

    public function getLogs($search = null, $offset = 0) {

        $search = urldecode($search);
        $this->response->setJSON();

        $logs = $this->logger->getLogs($search, $offset);
        $newOffset = $offset + count($logs);

        return json_encode(array("logs" => $logs, "offset" => $newOffset));

    }
    
    public function impersonate($starID) {

        setcookie("impersonateID", $starID, time() + (86400 * 30), "/");
        
        header("location: " . SITE_PATH . "/home/");
    
    }
    
    public function stopImpersonation() {

        setcookie("impersonateID", null, time() - 3600, "/");
        
        header("location: " . SITE_PATH . "/admin/users/");
    
    }


}