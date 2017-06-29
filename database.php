<?php
class DB extends Database {
	
	const ACTION_UNKNOWN 		= 0;
	const ACTION_BROWSE 		= 1;
	const ACTION_REGISTER 		= 2;
	const ACTION_LOGIN 			= 3;
	const ACTION_LOGOUT 		= 4;
	const ACTION_PLACE_BOOKING 	= 5;
	const ACTION_DELETE_BOOKING = 6;

	private $mode;

	public function __construct($host="localhost", $port=3306, $dbname=false, $user="root", $password=false) {
		$this->dbh = parent::__construct($host, $port, $dbname, $user, $password);
		$this->mode = self::ACTION_UNKNOWN;
		date_default_timezone_set("UTC");
	}
	
	/*
	 * Checks for vaild credentials
	 * @returns int $user_id | false
	*/
	public function IsVaildUser($user, $password) {
		$query = $this->query("SELECT id, password FROM users WHERE name = :name LIMIT 1", array(":name" => $user));
		if ( $query and !empty($query) ) {
			if (password_verify($password, $query[0]["password"])) {
				return $query[0]["id"];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/*
	 * Checks if given user is an administrator
	 * @returns bool true | false
	*/
	public function IsAdmin($userID) {
		$query = 'SELECT level from users where id = :userID';
		$args = array(':userID' => $userID);
		$results = $this->query($query, $args);
		return ($results[0]["level"] > 0 ? true : false);
	}
	
	/*
	 * Looks up user name
	 * @returns string username
	*/
	public function GetName($userID) {
		$query = 'SELECT name from users where id = :userID';
		$args = array(':userID' => $userID);
		$results = $this->query($query, $args);
		return $results[0]["name"];
	}
	
	/*
	 * Checks if name is avaiable for registration
	 * @returns bool true | false
	*/
	public function IsNameAvaiable($user) {
		$query = 'SELECT name from users where name = :user';
		$args = array(':user' => $user);
		$this->query($query, $args);
		return ($this->rowCount() > 0 ? false : true);
	}
	
	/*
	 * Creates user
	 * @returns bool true | false
	*/
	public function CreateUser($user, $password) {
		$query = 'INSERT INTO users (name, password) VALUES (:user, :password)';
		$args = array(':user' => $user, ':password' => password_hash($password, PASSWORD_BCRYPT));
		$this->query($query, $args);
		return ($this->rowCount() > 0 ? true : false);
	}
	
	/*
	 * Checks if we're allowed to place a booking for given day
	 * @returns bool true | false
	*/
	public function CanPlaceBooking($date) {
		$query = 'SELECT id from bookings where date = :date';
		$args = array(':date' => $date);
		$this->query($query, $args);
		return ($this->rowCount() > 0 ? false : true);
	}
	
	/*
	 * Places a booking for user for given date
	 * @returns bool true | false
	*/
	public function PlaceBooking($userID, $date) {
		$query = 'INSERT INTO bookings (date, user_id) VALUES (:date, :user_id)';
		$args = array(':user_id' => $userID, ':date' => $date);
		$this->query($query, $args);
		return ($this->rowCount() > 0 ? true : false);
	}
	
	/*
	 * Deletes a booking for user for given date
	 * @returns bool true | false
	*/
	public function DeleteBooking($userID, $date) {
		$query = 'DELETE FROM bookings WHERE user_id = :user_id AND date = :date';
		$args = array(':user_id' => $userID, ':date' => $date);
		$this->query($query, $args);
		return ($this->rowCount() > 0 ? true : false);
	}
	
	/*
	 * Gets bookings for all user or specific one
	 * @returns array
	*/
	public function GetBookings($userID = false) {
		if ( $userID ) {
			$query = 'SELECT date FROM bookings WHERE user_id = :user_id ORDER BY date ASC';
			$args = array(':user_id' => $userID);
		} else {
			$query = 'SELECT date FROM bookings WHERE date >= CURDATE() ORDER BY date ASC';
			$args = array();
		}
		$data = $this->query($query, $args);
		$newData = array();
		for ($i = 0, $size = count($data); $i < $size; ++$i) {
			$newData[$i] = $data[$i]["date"];
		}
		return $newData;
	}
	
	/*
	 * Gets bookings for all user in range of +/- 1 month, max 100 entries
	 * @returns array
	*/
	public function GetBookingsLog() {
		$query = '	SELECT users.name, bookings.date
					FROM users
					INNER JOIN bookings ON users.id = bookings.user_id
					WHERE
					date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND
					date <= DATE_ADD(CURDATE(), INTERVAL 1 MONTH)
					ORDER BY date ASC LIMIT 100';
		$args = array();
		$data = $this->query($query, $args);
		return $data;
	}
}
?>