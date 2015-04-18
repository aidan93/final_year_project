<?php 
	
	//restrict access to regular users
	$access = 'restricted';
	
	require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/session.php');

	$new_user = mysqli_real_escape_string($connect, $_POST['id']);
	$first_name = mysqli_real_escape_string($connect, $_POST['first_name']);
	$surname = mysqli_real_escape_string($connect, $_POST['surname']);
	$email = mysqli_real_escape_string($connect, $_POST['email']);
	$password = mysqli_real_escape_string($connect, $_POST['password']);

	if(strpos($new_user, "b00") !== false) {
		$course = mysqli_real_escape_string($connect, $_POST['course']);
	} else {
		$room = mysqli_real_escape_string($connect, $_POST['room']);
	}
	
	if(strpos($new_user, "b00") !== false) {
		$check_exists = "SELECT student_id FROM student WHERE student_id = '$new_user'";
	} else {
		$check_exists = "SELECT staff_id FROM staff WHERE staff_id = '$new_user'";
	}

	$check_query = mysqli_query($connect, $check_exists) or die (mysqli_error($connect));

	if(mysqli_num_rows($check_query) !== 0) {
		header("location: http://".$_SERVER['HTTP_HOST']."/project/admin.php?error=exists");
		exit;
	} else {
		$salt = sha1(md5(uniqid(rand(), true)));
		$password = sha1(md5(sha1($password) . md5($salt)));

		if(strpos($new_user, "b00") !== false) {
			$add_user = "INSERT INTO student (student_id, first_name, surname, course, email, password, salt) VALUES ('$new_user', '$first_name', '$surname', '$course', '$email', '$password', '$salt')";
		} else {
			$add_user = "INSERT INTO staff (staff_id, first_name, surname, room_number, email, availability, password, salt) VALUES ('$new_user', '$first_name', '$surname', '$room', '$email', 0, '$password', '$salt')";
		}

		$add_query = mysqli_query($connect, $add_user) or die (mysqli_error($connect));

		if($add_query) {
			header("location: http://".$_SERVER['HTTP_HOST']."/project/admin.php?status=created");
			exit;
		} else {
			header("location: http://".$_SERVER['HTTP_HOST']."/project/admin.php?error=insert");
			exit;
		}
	}
?>