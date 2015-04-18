<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/connect.php');

session_start(); // Starting session

$error = ""; // Variable to store the error message

// Check if user has submitted a username and password
if(isset($_POST['submit'])) {
	if(empty($_POST['username']) || empty($_POST['password'])) {
		
		$error = "<span class='error'>Username or Password is invalid. Please try again.</span>";
	} else {

		// To protect MySQL injection for security purposes
		$username = mysqli_real_escape_string($connect, $_POST['username']);
		$password = mysqli_real_escape_string($connect, $_POST['password']);

		$username = strtolower($username);

		//SQL query to get password salt for user
		if(strpos($username, "b00") !== false) {
			$salt = "SELECT salt FROM student WHERE student_id='$username'";
		} else {
			$salt = "SELECT salt FROM staff WHERE staff_id='$username'";
		}

		$salt_query = mysqli_query($connect, $salt);

		if(mysqli_num_rows($salt_query) === 1) {
			$row = mysqli_fetch_assoc($salt_query);
			$salt = $row['salt'];

			$password = sha1(md5(sha1($password) . md5($salt)));
		} else {
			$error = "<span class='error'>Username or Password is invalid. Please try again.</span>";
		}

		// SQL query to fetch information of registered users and finds user match
		if(strpos($username, "b00") !== false) {
			$sql = "SELECT * FROM student WHERE student_id='$username' AND password='$password'";
		} else {
			$sql = "SELECT * FROM staff WHERE staff_id='$username' AND password='$password'";
		}

		$query = mysqli_query($connect, $sql);

		if(mysqli_num_rows($query) === 1) {
			$_SESSION['login_user'] = $username; // Initialising Session
		} else {
			$error = "<span class='error'>Username or Password is invalid. Please try again.</span>";
		}
	}
}

if($_SESSION['login_user'] === 'e00000') {
	header("location: http://".$_SERVER['HTTP_HOST']."/project/admin.php");
	exit;
} else if(isset($_SESSION['login_user'])) {
	header("location: http://".$_SERVER['HTTP_HOST']."/project/views/profile.php?user=" . $_SESSION['login_user']);
	exit;
}			
?>