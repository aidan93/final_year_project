<?php

	//restrict access to regular users
	$access = 'restricted';
	
	require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/session.php');

	//Get search information
	$user = mysqli_real_escape_string($connect, $_POST['user']);

	//SQL query to get user details
	if(strpos($user, "b00") !== false) {
		$sql = "SELECT * FROM student WHERE student_id='$user'";
	} else {
		$sql = "SELECT * FROM staff WHERE staff_id='$user'";
	}

	$query = mysqli_query($connect, $sql) or die (mysqli_error($connect));

	if(mysqli_num_rows($query) === 1) {
		$row = mysqli_fetch_assoc($query);

		$first_name = $row['first_name'];
		$surname = $row['surname'];
		$email = $row['email'];
		if(strpos($user, "b00") !== false) {
			$course = $row['course'];
		} else {
			$room = $row['room_number'];
		}

		if(strpos($user, "b00") !== false) {
			$data = "<form action='/project/controller/admin_edit_user.php' method='post'>";
			$data .= "<li class='form_row'><label for='id' class='form_title'>User ID:</label><input type='text' name='id' id='user_id' value='" . $user . "' readonly='readonly'></li>";
			$data .= "<li class='form_row'><label for='first_name' class='form_title'>First Name:</label><input type='text' name='first_name' value='" . $first_name . "' required></li>";
			$data .= "<li class='form_row'><label for='surname' class='form_title'>Surname:</label><input type='text' name='surname' value='" . $surname . "' required></li>";
			$data .= "<li class='form_row'><label for='email' class='form_title'>Email:</label><input type='text' name='email' value='" . $email . "' required></li>";
			$data .= "<li class='form_row'><label for='course' class='form_title'>Course:</label><input type='text' name='course' value='" . $course . "' required></li>";
			$data .= "<li class='form_row'><label for='password' class='form_title'>Password:</label><input type='password' name='password'></li>";
		} else {
			$data = "<form action='/project/controller/admin_edit_user.php' method='post'>";
			$data .= "<li class='form_row'><label for='id' class='form_title'>User ID:</label><input type='text' name='id' id='user_id' value='" . $user . "' readonly='readonly'></li>";
			$data .= "<li class='form_row'><label for='first_name' class='form_title'>First Name:</label><input type='text' name='first_name' value='" . $first_name . "' required></li>";
			$data .= "<li class='form_row'><label for='surname' class='form_title'>Surname:</label><input type='text' name='surname' value='" . $surname . "' required></li>";
			$data .= "<li class='form_row'><label for='email' class='form_title'>Email:</label><input type='text' name='email' value='" . $email . "' required></li>";
			$data .= "<li class='form_row'><label for='room' class='form_title'>Room:</label><input type='text' name='room' value='" . $room . "' required></li>";
			$data .= "<li class='form_row'><label for='password' class='form_title'>Password:</label><input type='password' name='password'></li>";
		}
		
		$data .= "<input type='submit' id='edit_user'><button type='button' id='delete_user'>Delete User</button></form>";

	} else {
		$data = "No Results Found.";
	}

	echo $data;
?>