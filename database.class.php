<?php
date_default_timezone_set("UTC");
abstract class Database {
	private $dbh;
	private $query;
	private $queryResult;
	private $affectedRows;
	
	private $host;
	private $port;
	private $dbname;
	private $user;
	private $password;
	
	public function __construct($host="localhost", $port=3306, $dbname=false, $user="root", $password=false) {
		$this->host = $host;
		$this->port = $port;
		$this->dbname = $dbname;
		$this->user = $user;
		$this->password = $password;
		
		return $this->connect();
	}
	public function connect() {
		$this->dbh = new PDO(sprintf("mysql:host=%s;port=%d;dbname=%s", 
			$this->host,
			$this->port,
			$this->dbname),
			$this->user, $this->password);
		//$this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$this->dbh->exec("SET NAMES utf8");
		$this->dbh->exec("SET time_zone='+00:00'");
		return $this->dbh;
	}
	public function query($query, $args=array()) {
		$this->query = $this->dbh->prepare($query);
		$rows = $this->query->execute($args);
		$this->affectedRows = $this->query->rowCount();
		return $this->fetchResults();
	}
	public function fetchResults($fetch_style=PDO::FETCH_ASSOC) {
		$this->queryResult = $this->query->fetchAll($fetch_style);
		return $this->queryResult;
	}
	public function rowCount() {
		return $this->affectedRows;
	}
}
?>