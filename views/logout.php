<?php 
session_start();

if(session_destroy()) { // Destroying All Sessions
	header("location: http://".$_SERVER['HTTP_HOST']."/project/index.php");
}
?>