<?php
namespace Etech\Classes;

class Logging {
	
	protected $db;

	public function __construct($db) {
		$this->db = $db;
	}

	public function getLogs($search = NULL, $offset = 0, $limit = 25) {

		$limit = intval($limit);
		$offset = $offset ? intval($offset) : 0;

		$replacements = array();

		$filtering = "";
		if($search) {
			$filtering = " WHERE l.StarID LIKE :search OR l.Event LIKE :search OR l.Details LIKE :search OR l.Time LIKE :search OR CONCAT(u.Firstname, ' ', u.Lastname) LIKE :search";
			$replacements['search'] = "%".$search."%";
		}
		$filtering .= " ORDER BY Time DESC";

		if($limit) {
			$filtering .= " LIMIT " . $limit . ($offset ? " OFFSET " . $offset : "");
		}


		$logs = $this->db->query("SELECT u.Firstname, u.Lastname, u.StarID, l.Event, l.Details, l.Time 
			FROM Log AS l 
			LEFT JOIN Users AS u ON u.StarID = l.StarID " . $filtering, $replacements);

		foreach($logs AS $log) {
			$log->Time = !empty($log->Time) ? smartDate(strtotime($log->Time)) : "-";
		}

		return $logs;
	}

	public function logEvent($starID, $event, $details = NULL) {

		$date = getDateTime();

		$this->db->insert("Log", array("StarID" => $starID, "event" => $event, "details" => $details, "time" => $date));

		return true;

	}

}