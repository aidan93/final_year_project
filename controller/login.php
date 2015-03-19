<?php

include($_SERVER['DOCUMENT_ROOT'].'/project/controller/connect.php');

session_start(); // Starting session

$error = ""; // Variable to store the error message

// Check if user has submitted a username and password
if(isset($_POST['submit'])) {
	if(empty($_POST['username']) || empty($_POST['password'])) {
		
		$error = "<span class='error'>Username or Password is invalid. Please try again.</span>";
	} else {
		
		// Define $username and $password
		$username = $_POST['username'];
		$password = $_POST['password'];

		// To protect MySQL injection for security purposes
		$username = mysqli_real_escape_string($connect, $username);
		$password = mysqli_real_escape_string($connect, $password);

		$username = strtolower($username);

		// SQL query to fetch information of registered users and finds user match
		if(strpos($username, "b00") !== false) {
			$sql = "SELECT * FROM student WHERE student_id='$username' AND password='$password'";
		} else {
			$sql = "SELECT * FROM staff WHERE staff_id='$username' AND password='$password'";
		}

		$query = mysqli_query($connect, $sql);

		if(mysqli_num_rows($query) == 1) {
			$_SESSION['login_user'] = $username; // Initialising Session
		} else {
			$error = "<span class='error'>Username or Password is invalid. Please try again.</span>";
		}

		mysqli_close($connect);	// Close Connection
	}
}

if($_SESSION['login_user']) {
	header("location: http://".$_SERVER['HTTP_HOST']."/project/views/profile.php?user=" . $_SESSION['login_user']);
	exit;
}				
?>