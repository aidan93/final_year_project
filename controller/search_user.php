<?php
	
	//restrict access to regular users
	$access = 'restricted';

	require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/session.php');

	//Get search information
	$search = mysqli_real_escape_string($connect, $_POST['user']);

	//Check database for user with similar name to that of search input
	$staff_sql = "SELECT staff_id, first_name, surname FROM staff WHERE CONCAT(first_name, ' ', surname) LIKE '%$search%' OR surname LIKE '%$search%'";
	$staff_query = mysqli_query($connect, $staff_sql);

	$student_sql = "SELECT student_id, first_name, surname FROM student WHERE CONCAT(first_name, ' ', surname) LIKE '%$search%' OR surname LIKE '%$search%'";
	$student_query = mysqli_query($connect, $student_sql);

	//if there are results from the search then print out the relevant information
	if(mysqli_num_rows($staff_query) > 0 || mysqli_num_rows($student_query) > 0) {

		$staffno = mysqli_num_rows($staff_query);
		$studentno = mysqli_num_rows($student_query);
		$total =  $staffno + $studentno;

		$data = "<h3>" . $total . " " . "result(s) found</h3>";
		$data .= "<div id='search_header'><span class='user_id'>USER ID</span><span class='name'>NAME</span></div>";
		while($result = mysqli_fetch_assoc($staff_query)) {
			$user_id = $result['staff_id'];
			$first_name = $result['first_name'];
			$surname = $result['surname'];

			$data .= "<li class='search_result'><a href='#' class='result'><span class='user_id'>". $user_id ."</span><span class='name'>" . $first_name . " " . $surname . "</span></a></li>";
		}

		while($result = mysqli_fetch_assoc($student_query)) {
			$user_id = $result['student_id'];
			$first_name = $result['first_name'];
			$surname = $result['surname'];

			$data .= "<li class='search_result'><a href='#' class='result'><span class='user_id'>". $user_id ."</span><span class='name'>" . $first_name . " " . $surname . "</span></a></li>";
		}

		echo $data;
	} else {
		echo("<h3>No Results for '" . $search . "'</h3>");
	}
?>