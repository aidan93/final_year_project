<?php

//allow access to regular users
$access = 'allow';

require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/session.php');

//Get the user's id and selected times for event
$user_profile = mysqli_real_escape_string($connect, $_POST['user']);
$date = mysqli_real_escape_string($connect, $_POST['date']);
$selected_start_init = mysqli_real_escape_string($connect, $_POST['selected_start']);
$selected_end_init = mysqli_real_escape_string($connect, $_POST['selected_end']);

//if logged in user is student then use student 
if(strpos($user_check, "b00") !== false) {
	$student = mysqli_real_escape_string($connect, $user_check);
}

if($selected_start_init && $selected_end_init) {

	//format time to insert to mysql database
	$selected_start = strtotime($selected_start_init);
	$selected_end = strtotime($selected_end_init);
	$selected_start = date('H:i:s', $selected_start);
	$selected_end = date('H:i:s', $selected_end);

	if(strpos($user_profile, "b00") !== false) {
		$sql = "SELECT * FROM events WHERE student_id = '$user_profile' AND event_date = '$date' AND start_time = '$selected_start' AND end_time = '$selected_end'";
	} else {
		$sql = "SELECT * FROM events WHERE staff_id = '$user_profile' AND event_date = '$date' AND start_time = '$selected_start' AND end_time = '$selected_end'";
	}
	$query = mysqli_query($connect, $sql);

	if(mysqli_num_rows($query) > 0) {

		while ($result = mysqli_fetch_assoc($query)) {

			//if student id is available then get student_id, title and description
			if(isset($result['student_id'])) {
				$student = $result['student_id'];
				$title = $result['event_title'];
				$des = $result['description'];
			}
			$staff = $result['staff_id'];
			$location = $result['location'];
		}

		//Get staff members full name
		$sql_staff = "SELECT first_name, surname FROM staff WHERE staff_id = '$staff'";
		$query_staff = mysqli_query($connect, $sql_staff);
		while($result_staff = mysqli_fetch_assoc($query_staff)) {
			$staff = $result_staff['first_name'] .' '. $result_staff['surname'];
		}

		//Get students full name
		$sql_student = "SELECT first_name, surname FROM student WHERE student_id = '$student'";
		$query_student = mysqli_query($connect, $sql_student);
		while($result_student = mysqli_fetch_assoc($query_student)) {
			$student = $result_student['first_name'] .' '. $result_student['surname'];
		}

		//Create form to display data and allow user to add more detail to event
		if(strpos($user_check, "b00") !== false) {
			$form = "<form action='/project/controller/student_add_event.php' method='post'>";
			$form .= "<li class='form_row'><label for='staff' class='form_title'>Staff Member:</label><input type='text' name='staff' value='" . $staff . "' readonly='readonly'></li>";
			$form .= "<li class='form_row'><label for='student' class='form_title'>Student:</label><input type='text' name='student' value='" . $student . "' readonly='readonly'></li>";
			$form .= "<li class='form_row'><label for='title' class='form_title'>Event Title:</label><input type='text' name='title' value='" . $title . "' required></li>";
			$form .= "<li class='form_row'><label for='date' class='form_title'>Event Date:</label><input type='text' name='date' value='" . date("d/m/Y", strtotime($date)) . "' readonly='readonly'></li>";
			$form .= "<li class='form_row'><label for='start-time' class='form_title'>Event Start Time:</label><input type='text' name='start-time' value='" . $selected_start_init . "' readonly='readonly'></li>";
			$form .= "<li class='form_row'><label for='end-time' class='form_title'>Event End Time:</label><input type='text' name='end-time' value='" . $selected_end_init . "' readonly='readonly'></li>";
			$form .= "<li class='form_row'><label for='location' class='form_title'>location: </label><input type='text' name='location' value='" . $location . "' readonly='readonly'></li>";
			$form .= "<li class='form_row'><label for='description' class='form_title'>Description:</label><textarea name='description' cols='40' rows='6' required>" . $des . "</textarea></li>";
		} else {
			$form = "<form action='/project/controller/staff_add_event.php' method='post'>";
			$form .= "<li class='form_row'><label for='staff' class='form_title'>Staff Member:</label><input type='text' name='staff' value='" . $staff . "' readonly='readonly'></li>";
			$form .= "<li class='form_row'><label for='student' class='form_title'>Student:</label><input type='text' name='student' value='" . $student . "' readonly='readonly'></li>";
			$form .= "<li class='form_row'><label for='title' class='form_title'>Event Title:</label><input type='text' name='title' value='" . $title . "'></li>";
			$form .= "<li class='form_row'><label for='date' class='form_title'>Event Date:</label><input type='text' name='date' value='" . date("d/m/Y", strtotime($date)) . "' readonly='readonly'></li>";
			$form .= "<li class='form_row'><label for='start-time' class='form_title'>Event Start Time:</label><input type='text' name='start-time' value='" . $selected_start_init . "' readonly='readonly'></li>";
			$form .= "<li class='form_row'><label for='end-time' class='form_title'>Event End Time:</label><input type='text' name='end-time' value='" . $selected_end_init . "' readonly='readonly'></li>";
			$form .= "<li class='form_row'><label for='location' class='form_title'>location: </label><input type='text' name='location' value='" . $location . "'></li>";
			$form .= "<li class='form_row'><label for='description' class='form_title'>Description:</label><textarea name='description' cols='40' rows='6'>" . $des . "</textarea></li>";
			$form .= "<li class='form_row hidden'><input type='hidden' name='edit' value='edit'></li>";
		}
		
		$form .= "<input type='submit'></form>";

		echo $form;

	} else {

		echo("Error: " . mysqli_error($connect));
	}	
}

?>