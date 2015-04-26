<?php

//allow access to regular users
$access = 'allow';

require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/project/Google/autoload.php');

//Get the user's id and selected times for event
$staff = mysqli_real_escape_string($connect, $_POST['staff']);
if("" !== trim($_POST['title'])) {
	$title = mysqli_real_escape_string($connect, $_POST['title']);
} else {
	$title = NULL;
}
$date = mysqli_real_escape_string($connect, $_POST['date']);
$start_init = mysqli_real_escape_string($connect, $_POST['start-time']);
$end_init = mysqli_real_escape_string($connect, $_POST['end-time']);
if("" !== trim($_POST['description'])) {
	$des = mysqli_real_escape_string($connect, $_POST['description']);
} else {
	$des = NULL;
}

if($staff && $date && $start_init && $end_init) {

	if("" !== trim($_POST['location'])) {
		$location = mysqli_real_escape_string($connect, $_POST['location']);
	} else {
		$get_location = "SELECT room_number FROM staff WHERE staff_id = '$staff'";
		$query = mysqli_query($connect, $get_location);

		if(mysqli_num_rows($query) !== 0) {
			$row = mysqli_fetch_assoc($query);
			$location = $row['room_number'];
		}
	}

	if(isset($_POST['edit'])) {
		//format date to query mysql database
		list($day,$month,$year) = sscanf($date, "%d/%d/%d");
		if($day < 10) {
			$day = "0" . $day;
		}
		if($month < 10) {
			$month = "0" . $month;
		}
		$date = $year . '-' . $month . '-' . $day;

		//format times to query mysql database
		$start = date('H:i:s', strtotime($start_init));
	  	$end = date('H:i:s', strtotime($end_init));

	  	//get staff_id from staff table
	  	$parts = explode(" ", $staff);
		$surname = array_pop($parts);
		$firstname = implode(" ", $parts);
		$get_staff_id = "SELECT staff_id FROM staff WHERE first_name = '$firstname' AND surname = '$surname'";
		$staff_query = mysqli_query($connect, $get_staff_id);
		if(mysqli_num_rows($staff_query) !== 0) {
			$row = mysqli_fetch_assoc($staff_query);
			$staff = $row['staff_id'];
		}

		//Update event record to contain new information
		$sql = "UPDATE events SET event_title = '$title', location = '$location', description = '$des' WHERE staff_id = '$staff' AND event_date = '$date' AND start_time = '$start' AND end_time = '$end'";
		$update = mysqli_query($connect, $sql);

		//if update is successful, display confirmation message
		if($update) {
			//set status to editing event
			header('Location: http://'.$_SERVER['HTTP_HOST'].'/project/views/event_confirmation.php?status=Edited&start=' . $start . "&date=" . $date);
		} else {
			echo("Error: " . mysqli_error($connect));
		}
	} else {

		$t1 = strtotime($start_init);
	  	$t2 = strtotime($end_init);

	  	while($t1 < $t2) {
	  		//get start time of 30 min slot and add 30 min to it to get end time
	  		$start_time = $t1;
	  		$end_time = strtotime('+30 minutes', $t1);

	  		//convert times to readable times and insert to database
	  		$start_time = date('H:i:s', $start_time);
	  		$end_time = date('H:i:s', $end_time);

	  		$check_events = "SELECT event_date, start_time, end_time FROM events WHERE staff_id = '$staff' AND event_date = '$date' AND ((start_time <= '$start_time' AND end_time > '$start_time') OR (start_time < '$end_time' AND end_time >= '$end_time'))";
			$event_check = mysqli_query($connect, $check_events);

			//if there are no existing slots for this time then insert new event to database
			if(mysqli_num_rows($event_check) === 0) {
				$add_event = "INSERT INTO events (staff_id, event_title, event_date, start_time, end_time, location, description, status) VALUES ('$staff', '$title', '$date', '$start_time', '$end_time', '$location', '$des', 0)";
				$query = mysqli_query($connect, $add_event);
			} else {
				header('Location: http://'.$_SERVER['HTTP_HOST'].'/project/views/event_confirmation.php?status=Error&start=' . $_POST['start-time'] . '&end=' . $_POST['end-time'] . '&date=' . $date);
				exit;
			}

			$t1 = strtotime('+30 minutes', $t1);
	  	}

	  	$scriptUri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

		$client = new Google_Client();
		$client->setApplicationName("UUJ Electronic Noticeboard");
		$client->setDeveloperKey("AIzaSyD53jSGvnzDRwQxzHGIu6viFmGjFGzIQXA");  
		$client->setClientId('522850139708-ke5b3r8m9sqtt1fbhr6aleg1d5m5hso9.apps.googleusercontent.com');
		$client->setClientSecret('ND8xIzcEL3CNcHZsHeqM9zqM');
		$client->setRedirectUri($scriptUri);

		$client->setScopes(array('https://www.googleapis.com/auth/calendar'));

		$sql = "SELECT * FROM oauth_token WHERE staff_id = '$staff'";
		$token_query = mysqli_query($connect, $sql);

		// Run a quick check to verify there are any results
		$quick_check = mysqli_num_rows($token_query);

		if($quick_check !== 0) {
			$row = mysqli_fetch_assoc($token_query);
			$token_access = $row['access_token'];
			$token_type = $row['token_type'];
			$token_expire = $row['expires_in'];
			$token_refresh = $row['refresh_token'];
			$token_created = $row['created'];
			$token_user = $row['staff_id'];
			
			$array = array('access_token'=>$token_access, 'token_type'=>$token_type, 'expires_in'=>$token_expire, 'refresh_token'=>$token_refresh, 'created'=>$token_created);

			//If user's access token is expired, retrieve a new access token and update both the database record and array
			if($client->isAccessTokenExpired()) {
				$client->refreshToken($token_refresh);
		        $newtoken = $client->getAccessToken();
		        $token = json_decode($newtoken ,true);
				$token_access = $token['access_token'];
		        $tokenupdate = "UPDATE oauth_token SET access_token = '$token_access' WHERE refresh_token = '$token_refresh' AND staff_id = '$token_user'";
		        mysqli_query($connect, $tokenupdate);

		        $replacement = array('access_token'=>$token_access);
		        $array = array_replace($array, $replacement);
			}

			//Encode the array to a json array and gain access to the user's Google calendar
			$token = json_encode($array);
			$client->setAccessToken($token);
			$service = new Google_Service_Calendar($client);

			//Format start and end time for Google calendar event
			$start = date('H:i:s', strtotime($start_init));
	  		$end = date('H:i:s', strtotime($end_init));
			$gcal_start_time = $date . 'T' . $start;
			$gcal_end_time = $date . 'T' . $end;

			$event = new Google_Service_Calendar_Event();
			$event->setSummary('Free Student Time');
			$gcal_start = new Google_Service_Calendar_EventDateTime();
			$gcal_start->setTimeZone('Europe/London');
			$gcal_start->setDateTime($gcal_start_time);
			$event->setStart($gcal_start);
			$gcal_end = new Google_Service_Calendar_EventDateTime();
			$gcal_end->setTimeZone('Europe/London');
			$gcal_end->setDateTime($gcal_end_time);
			$event->setEnd($gcal_end);

			//Add event to user's Google calendar
			try {
				$createdEvent = $service->events->insert('primary', $event);
			} catch (Google_Service_Exception $e) {
				echo "An error has occurred with a Google Calendar request. Please return to the homepage. <br><br> <a href='/project/index.php'>Return Home</a>";
			}

			//If event has been successfully created in the user's Google calendar, apply the Google event ID to the event record it relates to in the database
			if(isset($createdEvent)) {
				$gcal_id = $createdEvent->getId();
				$sql = "UPDATE events SET cal_event_id = '$gcal_id' WHERE staff_id = '$staff' AND event_date = '$date' AND start_time >= '$start' AND end_time <= '$end'";
				$query = mysqli_query($connect, $sql);
				
				if($query) {
					header('Location: http://'.$_SERVER['HTTP_HOST'].'/project/views/event_confirmation.php?status=Free&start=' . $_POST['start-time'] . '&end=' . $_POST['end-time'] . '&date=' . $date);
				} else {
					echo("Error: " . mysqli_error($connect));
				}
			}
		} else {
			header('Location: http://'.$_SERVER['HTTP_HOST'].'/project/views/event_confirmation.php?status=Free&start=' . $_POST['start-time'] . '&end=' . $_POST['end-time'] . '&date=' . $date);
		}
	}

} else {
	echo "Missing some data.";
}

?>