<?php
include($_SERVER['DOCUMENT_ROOT'].'/project/controller/session.php');

$student = mysqli_real_escape_string($connect, $user_check);
$title = mysqli_real_escape_string($connect, $_POST['title']);
$date = mysqli_real_escape_string($connect, $_POST['date']);
$start_time = mysqli_real_escape_string($connect, $_POST['start-time']);
$end_time = mysqli_real_escape_string($connect, $_POST['end-time']);
$des = mysqli_real_escape_string($connect, $_POST['description']);

//format time to add to database
$start_time = strtotime($start_time);
$end_time = strtotime($end_time);
$start_time = date('H:i:s', $start_time);
$end_time = date('H:i:s', $end_time);

if($student && $title && $des) {

	//format date to insert to mysql database
	list($day,$month,$year) = sscanf($date, "%d/%d/%d");

	if($day < 10) {
		$day = "0" . $day;
	}

	if($month < 10) {
		$month = "0" . $month;
	}

	$date = $year . '-' . $month . '-' . $day;

	//sql query to update event table on student selecting event
	$sql = "UPDATE events SET student_id = '$student', event_title = '$title', description = '$des', status = 1 WHERE event_date = '$date' AND start_time = '$start_time' AND end_time = '$end_time'";
	$query = mysqli_query($connect, $sql);

	if($query) {
		echo "Success!";
	} else {
		echo("Error: " . mysqli_error($connect));
	}

} else {
	echo "All required details not submitted.";
}

mysqli_close($connect);
?>