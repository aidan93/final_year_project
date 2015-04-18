<?php 

require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/connect.php');

//Get all staff records
$sql = "SELECT staff_id, availability FROM staff";
$query = mysqli_query($connect, $sql) or die (mysqli_error($connect));

//If there are records available, for every staff member check their availability
if(mysqli_num_rows($query) > 0) {
	while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		$staff_id = $row['staff_id'];
		$day = date('Y-m-d');
		$time = date('H:i:s');

		//add 5 minutes to current time
		$time5 = date('H:i:s', strtotime('+5 minutes', strtotime($time)));

		$events_soon = "SELECT event_id, status FROM events WHERE staff_id = '$staff_id' AND event_date = '$day' AND start_time >= '$time' AND start_time <= '$time5'";
		$event_query = mysqli_query($connect, $events_soon) or die (mysqli_error($connect));

		//if there are any events in the next 5 mins, check to see if they are scheduled or free time
		if(mysqli_num_rows($event_query) > 0) {
			$events_row = mysqli_fetch_assoc($event_query);
			$status = $events_row['status'];

			//Check if event in next 5 mins is a scheduled or free time event and update staff availability accordingly
			if($status === '0') {
				$update = "UPDATE staff SET availability = '1' WHERE staff_id = '$staff_id'";
			} else if($status === '1') {
				$update = "UPDATE staff SET availability = '0' WHERE staff_id = '$staff_id'";
			}

			$update_query = mysqli_query($connect, $update) or die (mysqli_error($connect));
		}
	}
}

?>