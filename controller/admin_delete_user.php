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
		if(strpos($user, "b00") !== false) {
			$delete_user = "DELETE FROM student WHERE student_id = '$user'";
		} else {
			$delete_user = "DELETE FROM staff WHERE staff_id = '$user'";
		}
		
		$delete_query = mysqli_query($connect, $delete_user) or die (mysqli_error($connect));

		if($delete_query) {
			echo "/project/admin.php?status=deleted";
		} else {
			echo "/project/admin.php?error=delete";
		}
	}

?>