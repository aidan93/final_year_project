<?php 

//get user ID of logged in user
$user_profile = $_GET["user"];
$date = date("Y-m-d");

if(isset($user_profile)) {

	//if today is weekend then get the date of the next monday
	if((date('N', strtotime($date)) >= 6)) {
		$date = date('Y-m-d', strtotime('next Monday', strtotime($date)));
	}

	$sql = "SELECT * FROM events WHERE staff_id = '$user_profile' AND event_date = '$date'";
	
	//run sql query
	$query = mysqli_query($connect, $sql) or die (mysqli_error($connect));
	
	if(mysqli_num_rows($query) !== 0) {
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

		$events = "<div id='events'>";
		$events .= "<div id='event_header'>";
		$events .= "<span id='header_starttime'>START TIME</span>";
		$events .= "<span id='header_endtime'>END TIME</span></div>";
		$events .= "<div id='event_times'>";

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
			$events .= "<div class='timeslot' data-user-profile=".$user_profile." data-date=".$date."><span class='start_time'>".$value."</span><span class='end_time'>".$endTimes[$key]."</span><button type='button' class='delay_event'>Delay</button>" . $selectbox . "</div>";
		}

		$events .= "</div>";
		$events .= "<div id='buttons'>";
		$events .= "<button type='button' id='back'>Back</button></div></div>";
	} else {
		$events .= "<h3>You Have No Events Scheduled for Today.</h3>";
		$events .= "<button type='button' id='back'>Back</button>";
	}

	echo $events;
}
?>