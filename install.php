<?php
if ( file_exists("config.php") ) exit();

$config_template = '<?php
$settings["db"]["host"] 		= "%HOST%";
$settings["db"]["port"] 		= "%PORT%";
$settings["db"]["name"] 		= "%NAME%";
$settings["db"]["user"] 		= "%USER%";
$settings["db"]["password"] 	= "%PASSWORD%";
?>
';

$show_head = true;
$show_form = false;
$show_error = false;
$show_final = false;
$show_foot = true;
$error_msg = false;

$settings["db"]["host"] = false;
$settings["db"]["port"] = false;
$settings["db"]["name"] = false;
$settings["db"]["user"] = false;
$settings["db"]["password"] = false;
$settings["user"]["login"] = false;
$settings["user"]["password"] = false;

$languages = array("en_us", "pl_pl");
$lang = "en_us";

$L = array();
$L["en_us"] = array(
	"English" => "English",
	"Polish" => "Polski",
	"All Systems Online" => "All Systems Online",
	"You had ONE JOB" => "You had ONE JOB",
	"Database configuration" => "Database configuration",
	"Host" => "Host",
	"Port" => "Port",
	"Name" => "Name",
	"User" => "User",
	"Account configuration" => "Account configuration",
	"Callsign" => "Callsign",
	"Login" => "Login",
	"Password" => "Password",
	"Accept" => "Accept",
	"There is no such named database in the system" => "There is no such named database in the system",
	"Clear your database before installation" => "Clear your database before installation",
	"You didn't fill user information fields" => "You didn't fill user information fields",
	"System is now up and ready. Go to <a href=\"./index.php\">/index.php</a>" => "System is now up and ready. Go to <a href=\"./index.php\">/index.php</a>",
);
$L["pl_pl"] = array(
	"English" => "English",
	"Polish" => "Polski",
	"All Systems Online" => "Wszystkie systemy sprawne",
	"You had ONE JOB" => "Miałeś JEDNO zadanie",
	"Database configuration" => "Ustawienia bazy danych",
	"Host" => "Host",
	"Port" => "Port",
	"Name" => "Nazwa",
	"User" => "Użytkownik",
	"Account configuration" => "Konfiguracja konta",
	"Callsign" => "Znak wywoławczy",
	"Login" => "Login",
	"Password" => "Hasło",
	"Accept" => "Zatwierdź",
	"There is no such named database in the system" => "W systemie nie ma bazy danych o podanej nazwie",
	"Clear your database before installation" => "Wyczyść swoją bazę danych przed instalacją",
	"You didn't fill user information fields" => "Nie wypełniłeś pól z informacjami o użytkowniku",
	"System is now up and ready. Go to <a href=\"./index.php\">/index.php</a>" => "System jest już gotowy do pracy. Przejdź do <a href=\"./index.php\">/index.php</a>",
);
function T($translation_string, $return=false) {
	global $L;
	if ( isset($L[$_SESSION["lang"]][$translation_string]) ) {
		if ( $return ) return $L[$_SESSION["lang"]][$translation_string];
		echo $L[$_SESSION["lang"]][$translation_string];
	} else {
		if ( $return ) return "(N/A) " . $translation_string;
		echo "(N/A) " . $translation_string;
	}
}

session_start();

if ( !isset($_SESSION["lang"]) ) $_SESSION["lang"] = $lang;
if ( isset($_GET["lang"]) && in_array($_GET["lang"], $languages) ) $_SESSION["lang"] = $_GET["lang"];

if ( isset($_POST["host"]) && isset($_POST["port"]) && isset($_POST["name"]) &&
	isset($_POST["user"]) && isset($_POST["password"]) && 
	isset($_POST["user_login"]) && isset($_POST["user_password"]) ) {
	
	$settings["db"]["host"] = $_POST["host"];
	$settings["db"]["port"] = $_POST["port"];
	$settings["db"]["name"] = $_POST["name"];
	$settings["db"]["user"] = $_POST["user"];
	$settings["db"]["password"] = $_POST["password"];
	$settings["user"]["login"] = $_POST["user_login"];
	$settings["user"]["password"] = $_POST["user_password"];
	
	try {
		try {
			$dbh = new PDO(sprintf("mysql:host=%s;port=%d;dbname=%s", 
				$settings["db"]["host"],
				$settings["db"]["port"],
				$settings["db"]["name"]),
				$settings["db"]["user"], $settings["db"]["password"], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
				
			$query = $dbh->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :name");
			$query->execute(array(":name" => $settings["db"]["name"]));
			
			if ($query->rowCount() != 1) {
				throw new Exception(T("There is not such named database in the system", true));
			}
			
			$query = $dbh->prepare("SELECT COUNT(*) AS tables_found_count
							FROM information_schema.tables
							WHERE TABLE_SCHEMA = :name AND
							TABLE_NAME IN ('bookings', 'users')");
			$query->execute(array(":name" => $settings["db"]["name"]));
			$tables_count = $query->fetchAll(PDO::FETCH_ASSOC);
			
			if ( $tables_count[0]["tables_found_count"] > 0 ) {
				throw new Exception(T("Clear your database before installation", true));
			}
			
			if ( empty($settings["user"]["login"]) || empty($settings["user"]["password"]) ) {
				throw new Exception(T("You didn't fill user information fields", true));
			}
			
			/** begin installation **/
			
			$dbh->query("
				CREATE TABLE `users` (
					`id` MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
					`name` CHAR(50) NOT NULL COLLATE 'utf8_unicode_ci',
					`password` CHAR(255) NOT NULL COLLATE 'utf8_unicode_ci',
					`level` TINYINT(2) NOT NULL DEFAULT '0' COMMENT 'Account access level. 0 - default, 1 - admin',
					PRIMARY KEY (`id`),
					UNIQUE INDEX `name` (`name`)
				)
				COLLATE='utf8_unicode_ci'
				ENGINE=InnoDB");
			$dbh->query("
				CREATE TABLE `bookings` (
					`id` INT(11) NOT NULL AUTO_INCREMENT,
					`date` DATE NOT NULL,
					`user_id` MEDIUMINT(9) NOT NULL,
					PRIMARY KEY (`id`),
					UNIQUE INDEX `date` (`date`)
				)
				COLLATE='utf8_unicode_ci'
				ENGINE=InnoDB");
			$query = $dbh->prepare("INSERT INTO users (name, password, level) VALUES (:login, :password, 1)");
			$query->execute(array(
				":login" => $settings["user"]["login"], 
				":login" => $settings["user"]["login"], 
				":password" => password_hash($settings["user"]["password"], PASSWORD_BCRYPT)
			));
			
			$tmp = $config_template;
			$tmp = str_replace("%HOST%", $settings["db"]["host"], $tmp);
			$tmp = str_replace("%PORT%", $settings["db"]["port"], $tmp);
			$tmp = str_replace("%NAME%", $settings["db"]["name"], $tmp);
			$tmp = str_replace("%USER%", $settings["db"]["user"], $tmp);
			$tmp = str_replace("%PASSWORD%", $settings["db"]["password"], $tmp);
			file_put_contents("config.php", $tmp);
			
			$show_final = true;
			
		} catch( PDOException $Exception ) {
			$show_final = false;
			$show_form = true;
			$show_error = true;
			$error_msg = $Exception->getMessage();
		}
	} catch( Exception $Exception ) {
		$show_final = false;
		$show_form = true;
		$show_error = true;
		$error_msg = $Exception->getMessage();
	}
} else {
	$show_form = true;
}

if ($show_head) {
?>
<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>my page</title>
<style type="text/css">
html, body {
	color: #b2b7bb;
	background: url(shattered.png) repeat #E3E3E3;
	font: 19px "Arial", "Helvetica", "Clean", sans-serif;
	margin: 0px;
	padding: 0px;
	height: 100%;
}
section { width: auto; }
table { width: 100%; border-spacing: 0px; }
td { width: 50%; }
h1 { color: #e6c555; margin: 20px 0px; font-size: 24px; }
a { color: #fff; }
.error span {
	display: flex;
	background: #E65555;
	border-radius: 4px;
	padding: 5px;
	color: #f5f5f5;
	max-width: 400px;
}
.error h1 {
	color: #E65555;
}
.everything-ok h1 {
	color: #55E66F;
}
input[type="text"] {
	width: 100%;
}
input[type="text"]:focus {
	box-shadow: inset 0px 0px 0px 1px #E6C555;
	color: #E6C555;
}
input[type="text"], input[type="submit"] {
	background: #242424;
	color: #b2b7bb;
	font: 19px "Arial", "Helvetica", "Clean", sans-serif;
	padding: 5px;
	border: 0px;
}
input[type="submit"] {
	display: block;
	position: relative;
	top: 0px;
	cursor: pointer;
	background: #E6C555;
	background: -moz-linear-gradient(top, #e6c555 0%, #b29842 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#e6c555), color-stop(100%,#b29842));
	background: -webkit-linear-gradient(top, #e6c555 0%,#b29842 100%);
	background: -o-linear-gradient(top, #e6c555 0%,#b29842 100%);
	background: -ms-linear-gradient(top, #e6c555 0%,#b29842 100%);
	background: linear-gradient(to bottom, #e6c555 0%,#b29842 100%);
	box-shadow: 
		0px 3px 0px 0px #4D4734,
		0px 3px 0px 1px #262521,
		inset 0px 0px 0px 1px #E6C555;
	color: #2E2E2E;
	padding: 10px 25px;
	margin-top: 20px;
	border-radius: 4px;
}
input[type="submit"]:hover {
	box-shadow:
		0px 3px 0px 0px #4D4734,
		0px 3px 0px 1px #262521,
		inset 0px 0px 0px 1px #E6C555;
}
input[type="submit"]:active {
	color: #4D4734;
	top: 3px;
	box-shadow: 
		0px 0px 0px 1px #262521,
		inset 0px 0px 0px 1px #E6C555;
}
#contanyan {
	width: 100%;
	height: 100%;
}
#contenyan {
	max-width: 400px; /* Thanks Google Chrome! You absolutely disgusting browser! */
	background: url(footer_lodyas.png) repeat #2E2E2E;
	padding: 0px 35px 15px 35px;
	border-radius: 4px;
	box-shadow: 0px 0px 0px 1px #fff, inset 0px 0px 15px rgba(0,0,0, 0.5), inset 0px 0px 3px 1px rgba(0,0,0, 0.5);
}
.flex-box {
	display: flex;
	justify-content: center;
	align-items: center;
	align-content: center;
}
.flex-column {
	flex-direction: column;
}

tr:hover td {
	background: rgba(0,0,0, 0.2);
}
td span {
	padding-left: 5px;
}
tr:hover td:first-child {
	box-shadow: -5px 0px 0px #E6C555;
}
section { width: 100%; }
section.sa { width: auto; }

section.lang {
	margin-top: 20px;
	padding-top: 10px;
	border-top: 1px dashed #242424;
}
ul, li {
	margin: 0;
	padding: 0;
	list-style-type: none;
	font-size: 13px;
}
li { display: inline; }
li a { text-decoration: none; color: #808487; }
li a:hover { text-decoration: underline; color: #E6C555; }
</style>
</head>
<body>
<div id="contanyan" class="flex-box">
	<article id="contenyan" class="flex-item">
		<div class="flex-box flex-column">
<?php
} // show head
if ($show_final) {
?>
			<section class="everything-ok">
				<header>
					<h1>&#10003; <?php T("All Systems Online"); ?></h1>
				</header>
				<span><?php T("System is now up and ready. Go to <a href=\"./index.php\">/index.php</a>"); ?></span>
			</section>
<?php
} // end installation
if ($show_error) {
?>
			<section class="error">
				<header>
					<h1>&#9888; <?php T("You had ONE JOB"); ?></h1>
				</header>
				<span><?php echo $error_msg; ?></span>
			</section>
<?php
} // show error
if ($show_form) {
?>
			<section>
				<header>
					<h1><?php T("Database configuration"); ?></h1>
				</header>
				<form action="<?php echo basename(__FILE__); ?>" method="post">
				<table>
					<tr>
						<td>
							<span><?php T("Host"); ?></span>
						</td>
						<td>
							<input type="text" name="host" value="<?php echo ($settings["db"]["host"] ? $settings["db"]["host"] : 'localhost'); ?>">
						</td>
					</tr>
					<tr>
						<td>
							<span><?php T("Port"); ?></span>
						</td>
						<td>
							<input type="text" name="port" value="<?php echo ($settings["db"]["port"] ? $settings["db"]["port"] : '3306'); ?>">
						</td>
					</tr>
					<tr>
						<td>
							<span><?php T("Name"); ?></span>
						</td>
						<td>
							<input type="text" name="name" value="<?php echo ($settings["db"]["name"] ? $settings["db"]["name"] : ''); ?>">
						</td>
					</tr>
					<tr>
						<td>
							<span><?php T("User"); ?></span>
						</td>
						<td>
							<input type="text" name="user" value="<?php echo ($settings["db"]["user"] ? $settings["db"]["user"] : 'root'); ?>">
						</td>
					</tr>
					<tr>
						<td>
							<span><?php T("Password"); ?></span>
						</td>
						<td>
							<input type="text" name="password" value="<?php echo ($settings["db"]["password"] ? $settings["db"]["password"] : ''); ?>">
						</td>
					</tr>
				</table>
			</section>
			<section>
				<header>
					<h1><?php T("Account configuration"); ?></h1>
				</header>
				<table>
					<tr>
						<td>
							<span><?php T("Login"); ?></span>
						</td>
						<td>
							<input type="text" name="user_login" value="<?php echo ($settings["user"]["login"] ? $settings["user"]["login"] : ''); ?>">
						</td>
					</tr>
					<tr>
						<td>
							<span><?php T("Password"); ?></span>
						</td>
						<td>
							<input type="text" name="user_password" value="<?php echo ($settings["user"]["password"] ? $settings["user"]["password"] : ''); ?>">
						</td>
					</tr>
				</table>
			</section>
			<section class="sa">
				<div class="button">
					<input type="submit" value="<?php T("Accept"); ?>">
				</div>
			</section>
			<section class="lang">
				<ul>
					<li><a href="?lang=en_us"><?php T("English"); ?></a></li>
					<li><a href="?lang=pl_pl"><?php T("Polish"); ?></a></li>
				</ul>
			</section>
<?php
} // show form
if ($show_foot) {
?>
		</div>
	</article>
</div>
</body>
</html>
<?php
}
?>