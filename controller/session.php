<?php 

include($_SERVER['DOCUMENT_ROOT'].'/project/controller/connect.php');

// Start session
session_start();

// Store session
$user_check = $_SESSION['login_user'];

// SQL query to fetch username
if(strpos($user_check, "b00") !== false) {
	$sql = "SELECT * FROM student WHERE student_id='$user_check'";
} else {
	$sql = "SELECT * FROM staff WHERE staff_id='$user_check'";
}

$query = mysqli_query($connect, $sql);

$row = mysqli_fetch_assoc($query);
$login_session = $row['first_name'] . ' ' . $row['surname'];


if(!isset($user_check)) {
	mysqli_close($conn);
	header("location: http://".$_SERVER['HTTP_HOST']."/project/index.php");
	exit;
}

?>