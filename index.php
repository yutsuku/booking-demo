<?php
if ( file_exists("config.php") ) {
	header("HTTP/1.1 301 Moved Permanently"); 
	header("Location: ./controller.php"); 
	exit();
} else {
	header("Location: ./install.php"); 
	exit();
}
?>