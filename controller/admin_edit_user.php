<?php
	
	//restrict access to regular users
	$access = 'restricted';

	require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/session.php');

	if("" !== trim($_POST['id'])) {
		$user = mysqli_real_escape_string($connect, $_POST['id']);
	}

	if("" !== trim($_POST['first_name'])) {
		$first_name = mysqli_real_escape_string($connect, $_POST['first_name']);
	}
	
	if("" !== trim($_POST['surname'])) {
		$surname = mysqli_real_escape_string($connect, $_POST['surname']);	
	}

	if("" !== trim($_POST['email'])) {
		$email = mysqli_real_escape_string($connect, $_POST['email']);
	}

	if("" !== trim($_POST['course'])) {
		$course = mysqli_real_escape_string($connect, $_POST['course']);
	}

	if("" !== trim($_POST['room'])) {
		$room = mysqli_real_escape_string($connect, $_POST['room']);
	}

	if("" !== trim($_POST['id']) && "" !== trim($_POST['password'])) {

		$password = mysqli_real_escape_string($connect, $_POST['password']);

		//SQL query to get password salt for user
		if(strpos($user, "b00") !== false) {
			$salt = "SELECT salt FROM student WHERE student_id='$user'";
		} else {
			$salt = "SELECT salt FROM staff WHERE staff_id='$user'";
		}

		$salt_query = mysqli_query($connect, $salt) or die (mysqli_error($connect));

		if(mysqli_num_rows($salt_query) === 1) {
			$row = mysqli_fetch_assoc($salt_query);
			$salt = $row['salt'];

			$password = sha1(md5(sha1($password) . md5($salt)));
		} else {
			$new_salt = sha1(md5(uniqid(rand(), true)));
			$password = sha1(md5(sha1($password) . md5($new_salt)));
		}
	}

	if(strpos($user, "b00") !== false && "" !== trim($_POST['password'])) {
		$sql = "UPDATE student SET first_name='$first_name', surname='$surname', email='$email', course='$course', password='$password' WHERE student_id='$user'";
	} else if(strpos($user, "b00") !== false && "" === trim($_POST['password'])) {
		$sql = "UPDATE student SET first_name='$first_name', surname='$surname', email='$email', course='$course' WHERE student_id='$user'";
	} else if(strpos($user, "b00") === false && "" !== trim($_POST['password'])) {
		$sql = "UPDATE staff SET first_name='$first_name', surname='$surname', email='$email', room_number='$room', password='$password' WHERE staff_id='$user'";
	} else {
		$sql = "UPDATE staff SET first_name='$first_name', surname='$surname', email='$email', room_number='$room' WHERE staff_id='$user'";
	}

	$query = mysqli_query($connect, $sql) or die (mysqli_error($connect));

	if($query) {
		header("location: http://".$_SERVER['HTTP_HOST']."/project/admin.php?status=edited");
		exit;
	} else {
		header("location: http://".$_SERVER['HTTP_HOST']."/project/admin.php?error=insert");
		exit;
	}

?>