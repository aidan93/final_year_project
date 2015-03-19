<?php 
include($_SERVER['DOCUMENT_ROOT'].'/project/controller/connect.php');

//get date and user ID of profile owner
$date = $_GET["date"];
$user_profile = $_GET["user"];

//get if logged in user is a student or staff and select the relevant events in accordance with this
if(strpos($_SESSION['login_user'], "b00") !== false) {
	
	if($_SESSION['login_user'] !== $user_profile) {

		$sql = "SELECT * FROM events WHERE staff_id = '$user_profile' AND event_date = '$date' AND status = 0";
	} else if($_SESSION['login_user'] == $user_profile) {

		$sql = "SELECT * FROM events WHERE student_id = '$user_profile' AND event_date = '$date' AND status = 1";
	}
} else {

	if($_SESSION['login_user'] === $user_profile) {

		$sql = "SELECT * FROM events WHERE staff_id = '$user_profile' AND event_date = '$date' AND status = 1";
	}
}

//run sql query
$query = mysqli_query($connect, $sql) or die (mysqli_error($connect));

//create arrays for both start and end times
$startTimes = [];
$endTimes = [];

//go through each of the events returned from the query and get their start and end times
while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {

	$start_time = new DateTime($row['start_time']);
	$start_time = $start_time->format('H:i');
	$end_time = new DateTime($row['end_time']);
	$end_time = $end_time->format('H:i');

	array_push($startTimes, $start_time);
	array_push($endTimes, $end_time);
}

//sort the arrays in numerical order
asort($startTimes);
asort($endTimes);

//for each individual time in array, print out the relevant information to screen
foreach($startTimes as $key => $value)
{
	if(strpos($_SESSION['login_user'], "b00") !== false && $_SESSION['login_user'] !== $user_profile) {
		echo "<div class='timeslot' data-user-profile=".$user_profile." data-date=".$date."><span class='start_time'>".$value."</span><span class='end_time'>".$endTimes[$key]."</span><a class='choose_time'></a></div>";
	} else {
		echo "<div class='timeslot' data-user-profile=".$user_profile." data-date=".$date."><span class='start_time'>".$value."</span><span class='end_time'>".$endTimes[$key]."</span><button type='button' class='view_event'>View</a></div>";
	}
}

mysqli_close($connect);
?>