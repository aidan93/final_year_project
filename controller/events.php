<?php 
require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/connect.php');

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

		$sql = "SELECT * FROM events WHERE staff_id = '$user_profile' AND event_date = '$date'";
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

//set up select box to display delay duration times
$selectbox = "<select class='delay_times'><option value='0'>No Delay</option>";
$selectbox .= "<option value='5'>5 Minutes</option>";
$selectbox .= "<option value='10'>10 Minutes</option>";
$selectbox .= "<option value='15'>15 Minutes</option>";
$selectbox .= "<option value='20'>20 Minutes</option></select>";

//for each individual time in array, print out the relevant information to screen
foreach($startTimes as $key => $value)
{
	if(strpos($_SESSION['login_user'], "b00") !== false && $_SESSION['login_user'] !== $user_profile) {
		echo "<div class='timeslot' data-user-profile=".$user_profile." data-date=".$date."><span class='start_time'>".$value."</span><span class='dash'> - </span><span class='end_time'>".$endTimes[$key]."</span><a class='choose_time'></a></div>";
	} else if(strpos($_SESSION['login_user'], "b00") !== false && $_SESSION['login_user'] === $user_profile) {
		echo "<div class='timeslot' data-user-profile=".$user_profile." data-date=".$date."><span class='start_time'>".$value."</span><span class='dash'> - </span><span class='end_time'>".$endTimes[$key]."</span><button type='button' class='delete_event'>Delete</button><button type='button' class='view_event'>View</button></div>";
	} else {
		echo "<div class='timeslot' data-user-profile=".$user_profile." data-date=".$date."><span class='start_time'>".$value."</span><span class='dash'> - </span><span class='end_time'>".$endTimes[$key]."</span><button type='button' class='delete_event'>Delete</button><button type='button' class='delay_event'>Delay</button>" . $selectbox . "<button type='button' class='view_event'>View</button></div>";
	}
}
?>