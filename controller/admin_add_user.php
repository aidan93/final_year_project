<?php 

	//restrict access to regular users
	$access = 'restricted';
	
	require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/session.php');

	//Process data submitted from form
	$new_user = mysqli_real_escape_string($connect, $_POST['id']);

	//Make sure length of user ID is correct
	$len = strlen($new_user);
	if(strpos($new_user, "b00") !== false) {
		if($len !== 9){
	        header("location: http://".$_SERVER['HTTP_HOST']."/project/admin.php?error=insert");
			exit;
	    }
	} else {
		if($len !== 6){
	        header("location: http://".$_SERVER['HTTP_HOST']."/project/admin.php?error=insert");
			exit;
	    }
	}

	$first_name = mysqli_real_escape_string($connect, $_POST['first_name']);
	$surname = mysqli_real_escape_string($connect, $_POST['surname']);
	$email = mysqli_real_escape_string($connect, $_POST['email']);

	$password = mysqli_real_escape_string($connect, $_POST['password']);

	//If password does not contain at least 1 uppercase letter, at least 1 lowercase letter, at least 1 number and is less than 8 characters long, display error
	$uppercase = preg_match('@[A-Z]@', $password);
	$lowercase = preg_match('@[a-z]@', $password);
	$number    = preg_match('@[0-9]@', $password);
	if(!$uppercase || !$lowercase || !$number || strlen($password) < 8) {
	   	header("location: http://".$_SERVER['HTTP_HOST']."/project/admin.php?error=password");
		exit;
	}

	//If new user is a student then get their course details or if new user is a member of staff get their room details
	if(strpos($new_user, "b00") !== false) {
		$course = mysqli_real_escape_string($connect, $_POST['course']);
	} else {
		$room = mysqli_real_escape_string($connect, $_POST['room']);
	}
	
	//Check if user already exists
	if(strpos($new_user, "b00") !== false) {
		$check_exists = "SELECT student_id FROM student WHERE student_id = '$new_user'";
	} else {
		$check_exists = "SELECT staff_id FROM staff WHERE staff_id = '$new_user'";
	}

	$check_query = mysqli_query($connect, $check_exists);

	/*
	** If user exists then make admin aware 
	** If they do not exist then create a new user based on the details provided
	*/
	if(mysqli_num_rows($check_query) !== 0) {
		header("location: http://".$_SERVER['HTTP_HOST']."/project/admin.php?error=exists");
		exit;
	} else {

		//Encrypt password
		$salt = sha1(md5(uniqid(rand(), true)));
		$password = sha1(md5(sha1($password) . md5($salt)));

		//If new user is a student, add to student table or if new user is a staff member, add to staff table
		if(strpos($new_user, "b00") !== false) {
			$add_user = "INSERT INTO student (student_id, first_name, surname, course, email, password, salt) VALUES ('$new_user', '$first_name', '$surname', '$course', '$email', '$password', '$salt')";
		} else {
			$add_user = "INSERT INTO staff (staff_id, first_name, surname, room_number, email, availability, password, salt) VALUES ('$new_user', '$first_name', '$surname', '$room', '$email', 0, '$password', '$salt')";
		}

		$add_query = mysqli_query($connect, $add_user);

		/*
		** If inserting new user was successful, display success message to admin
		** IF inserting new user was unsuccessful, display error message to admin
		*/
		if($add_query) {
			header("location: http://".$_SERVER['HTTP_HOST']."/project/admin.php?status=created");
			exit;
		} else {
			header("location: http://".$_SERVER['HTTP_HOST']."/project/admin.php?error=insert");
			exit;
		}
	}
?>