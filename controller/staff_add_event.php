<?php

include($_SERVER['DOCUMENT_ROOT'].'/project/controller/connect.php');

//Get the user's id and selected times for event
$staff = mysqli_real_escape_string($connect, $_POST['staff']);
if(isset($_POST['title'])) {
	$title = mysqli_real_escape_string($connect, $_POST['title']);
} else {
	$title = "NULL";
}
$date = mysqli_real_escape_string($connect, $_POST['date']);
$start_init = mysqli_real_escape_string($connect, $_POST['start-time']);
$end_init = mysqli_real_escape_string($connect, $_POST['end-time']);
$location = mysqli_real_escape_string($connect, $_POST['location']);
if(isset($_POST['description'])) {
	$des = mysqli_real_escape_string($connect, $_POST['description']);
} else {
	$des = "NULL";
}

if($staff && $date && $start_init && $end_init && $location) {

	//format time to insert to mysql database
	$start = strtotime($start_init);
	$end = strtotime($end_init);
	$start = date('H:i:s', $start);
	$end = date('H:i:s', $end);

	$sql = "INSERT INTO events (staff_id, event_title, event_date, start_time, end_time, location, description, status) VALUES ('$staff', '$title', '$date', '$start', '$end', '$location', '$des', 0)";

	$query = mysqli_query($connect, $sql);

	if($query) {
		header("location: http://".$_SERVER['HTTP_HOST']."/project/index.php");
	} else {
		echo("Error: " . mysqli_error($connect));
	}

} else {
	echo "Missing some data.";
}

mysqli_close($connect);

?>