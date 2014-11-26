<?php
session_start(); // Starting session

$error = ""; // Variable to store the error message

// Check if user has submitted a username and password
if(isset($_POST['submit'])) {
	if(empty($_POST['username']) || empty($_POST['password'])) {
		
		$error = "Username or Password is invalid";
	} else {
		
		// Define $username and $password
		$username = $_POST['username'];
		$password = $_POST['password'];

		// Connect with database
		$conn = mysql_connect("localhost", "root", "root");

		// To protect MySQL injection for security purposes
		$username = stripslashes($username);
		$password = stripslashes($password);

		$username = mysql_real_escape_string($username);
		$password = mysql_real_escape_string($password);

		$username = strtolower($username);

		// Selecting database
		$db = mysql_select_db("project", $conn);

		// SQL query to fetch information of registered users and finds user match
		if(strpos($username, "b00") !== false) {
			$query = mysql_query("SELECT * FROM student WHERE student_id='$username' AND password='$password'", $conn);
		} else {
			$query = mysql_query("SELECT * FROM lecturer WHERE staff_id='$username' AND password='$password'", $conn);
		}

		$rows = mysql_num_rows($query);

		if($rows == 1) {
			$_SESSION['login_user'] = $username; // Initialising Session
			header("location: home.php"); // Redirecting to home page
		} else {
			$error = "Username or Password is invalid";
		}

		mysql_close($conn);	// Close Connection
	}
}				
?>