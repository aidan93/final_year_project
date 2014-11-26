<?php 

// Establish server connection
$conn = mysql_connect("localhost", "root", "root");

// Selecting database
$db = mysql_select_db("project", $conn);

// Start session
session_start();

// Store session
$user_check = $_SESSION['login_user'];

// SQL query to fetch username
if(strpos($user_check, "b00") !== false) {
	$ses_sql = mysql_query("SELECT student_id FROM student WHERE student_id='$user_check'", $conn);
} else {
	$ses_sql = mysql_query("SELECT staff_id FROM lecturer WHERE staff_id='$user_check'", $conn);
}

$row = mysql_fetch_assoc($ses_sql);

if(strpos($user_check, "b00") !== false) {
	$login_session = $row['student_id'];
} else {
	$login_session = $row['staff_id'];
}


if(!isset($login_session)) {
	mysql_close($conn);
	header("location: index.php");
}
?>