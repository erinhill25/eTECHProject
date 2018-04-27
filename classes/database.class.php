<?php
namespace Etech\Classes;
    
class Database extends \PDO {

	private $dbh, $queryCount;
	
	public function __construct()
	{	
		try {
		parent::__construct("mysql:host=" . HOST .";port=" . DB_PORT . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
		}
		catch(\Exception $e) {
			echo $e->getMessage();
		}
	}
	
	public function query($query, $fields = NULL, $fetchType = "ALL", $fetchMode = \PDO::FETCH_OBJ)
	{
		$this->queryCount++;
		$getType = explode(" ", $query);
		$type = strtoupper($getType[0]);
		switch($type)
		{
			case "SELECT":
				return $this->selectQuery($query,$fields,$fetchType,$fetchMode);
			break;
			case "INSERT":
				return $this->insertQuery($query,$fields);
			break;
			case "UPDATE":
				return $this->updateQuery($query,$fields);
			break;
			case "DELETE":
				return $this->deleteQuery($query,$fields);
			break;
			default:
				return $this->defaultQuery($query);
		}
	}
	
	protected function defaultQuery($query)
	{
		$STH = parent::query($query);  
	}
	
	public function insertQuery($query, $fields)
	{
	    try {
			$STH = $this->prepare($query); 
			$STH->execute($fields);
			return $this->lastInsertId("id"); 
		}
		catch(PDOException $e) {
			echo $e->getMessage(); 
			foreach ($fields AS $field=>$value) {
				$query = str_replace(":{$field}", "'{$value}'", $query);
			}
            echo $query;
		}
	}

	public function getQueryCount()
	{
		return $this->queryCount;
	}

	public function insert($table, $fields)
	{
		$query = "INSERT INTO " . $table  . " (" . implode(",", array_keys($fields)) . ") VALUES (:" . implode(",:",  array_keys($fields)) . ")";
		return $this->query($query, $fields); 
	}
	
    public function updateQuery($query, $fields)
	{
		$STH = $this->prepare($query); 
		$STH->execute($fields); 
	}
	
	
	public function selectQuery($query, $fields, $fetchType = "ALL", $fetchMode = \PDO::FETCH_OBJ)
	{
		/*foreach ($fields AS $field=>$value) {
			$query = str_replace(":{$field}", "'{$value}'", $query);
		}
		echo $query;*/
		
		try {
			$STH = $this->prepare($query); 
			$STH->execute($fields); 		
	    }
		catch(PDOException $e) {  
			echo $e->getMessage();  
		} 	
		$STH->setFetchMode($fetchMode); 
		if($fetchType == "ALL") {
			return $STH->fetchAll();
		}
		else {
			return $STH->fetch();
		}
	 
	}
	
	public function deleteQuery($query,$fields)
	{
		$STH = $this->prepare($query); 
		$STH->execute($fields); 
	}
	
}
