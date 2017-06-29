<?php
define("APP", true);

// http://php.net/manual/en/function.checkdate.php#113205
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

try {
	include "config.php";
	include "database.class.php";
	include "database.php";

	session_start();

	$DB = new DB($settings["db"]["host"], $settings["db"]["port"], $settings["db"]["name"], $settings["db"]["user"], $settings["db"]["password"]);
	
	if ( isset($_GET["logout"]) ) {
		session_destroy();
		header("Location: http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}");
		exit();
	}
	elseif ( isset($_POST["login"]) && isset($_POST["username"]) && isset($_POST["password"])) {
		// handle login form
		$userID = $DB->IsVaildUser($_POST["username"], $_POST["password"]);
		if ( $userID !== false ) {
			session_regenerate_id(true);
			
			$_SESSION["logged_in"] = true;
			$_SESSION["user_id"] = $userID;
			$_SESSION["user_name"] = htmlentities($DB->GetName($userID), ENT_QUOTES | ENT_IGNORE, "UTF-8");
			$_SESSION["user_admin"] = $DB->IsAdmin($userID);
		} else {
			// error (login and/or password mismatch)
		}
	}
	elseif ( isset($_POST["register"]) && isset($_POST["username"]) && isset($_POST["password"])) {
		// handle register form
		if ( $DB->IsNameAvaiable($_POST["username"]) ) {
			$success = $DB->CreateUser($_POST["username"], $_POST["password"]);
			if ( $success ) {
				// authorize user durning registration
				$userID = $DB->IsVaildUser($_POST["username"], $_POST["password"]);
				if ( $userID !== false ) {
					session_regenerate_id(true);
					$_SESSION["logged_in"] = true;
					$_SESSION["user_id"] = $userID;
					$_SESSION["user_name"] = htmlentities($DB->GetName($userID), ENT_QUOTES | ENT_IGNORE, "UTF-8");
					$_SESSION["user_admin"] = $DB->IsAdmin($userID);
				}
			} else {
				// error (unknown; making new user)
			}
		} else {
			// error (name not avaiable)
		}
	}
	elseif ( isset($_POST["booking-new"]) && isset($_POST["date"]) && isset($_SESSION["logged_in"]) && isset($_SESSION["user_id"])) {
		// handle booking
		if ( validateDate($_POST["date"]) ) {
			if ( $DB->CanPlaceBooking($_POST["date"]) ) {
				$success = $DB->PlaceBooking($_SESSION["user_id"], $_POST["date"]);
				if ( $success ) {
					// make sure user grabs new data
					header("Location: http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}");
				} else {
					// error (unknown; placing a booking)
				}
			} else {
				// error (already booked for this day)
			}
		} else {
			// error (invaild date)
		}
	}
	elseif ( isset($_GET["booking-cancel"]) && validateDate($_GET["booking-cancel"]) && isset($_SESSION["logged_in"]) && isset($_SESSION["user_id"])) {
		$success = $DB->DeleteBooking($_SESSION["user_id"], $_GET["booking-cancel"]);
		if ( $success ) {
			// make sure user grabs new data
			header("Location: http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}");
		} else {
			// error (unnown; unable to delete booking)
		}
	}
	
	$all_bookings = json_encode($DB->GetBookings());
	$my_bookings = json_encode(array());
	
	if ( isset($_SESSION["logged_in"]) && isset($_SESSION["user_id"]) ) {
		$my_bookings = json_encode($DB->GetBookings($_SESSION["user_id"]));
	}
	
	include "templates/head.php";
	
	if (isset($_SESSION["logged_in"])) {
		include "templates/main-user.php";
		include "templates/booking-new.php";
		include "templates/booking-cancel.php";
		if ( $_SESSION["user_admin"] ) {
			include "templates/system-logs.php";
		}
	} else {
		include "templates/main.php";
		include "templates/register.php";
		include "templates/login.php";
	}
	
	include "templates/foot.php";
	
} catch(PDOException $e) {
	$error_line1 = sprintf("%d: %s", $e->getCode(), $e->getMessage());
	$error_line2 = sprintf("In %s at line <b>%d</b>", $e->getFile(), $e->getLine());
	$error_line3 = $e->getTraceAsString();
	include "templates/system-error.php";
} catch(Exception $e) {
	$error_line1 = sprintf("%d: %s", $e->getCode(), $e->getMessage());
	$error_line2 = sprintf("In %s at line <b>%d</b>", $e->getFile(), $e->getLine());
	$error_line3 = $e->getTraceAsString();
	include "templates/system-error.php";
}
?>