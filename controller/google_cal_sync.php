<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/project/Google/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/connect.php');

//Get all the records for google calendars linked to staff
$sql = "SELECT * FROM oauth_token WHERE staff_id IS NOT NULL";
$query = mysqli_query($connect, $sql);

// Run a quick check to verify there are any results
$quick_check = mysqli_num_rows($query);

if($quick_check !== 0) {

	while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {

		//Establish connection to system's Google calendar project
		$scriptUri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
		$client = new Google_Client();
		$client->setApplicationName("UUJ Electronic Noticeboard");
		$client->setDeveloperKey("AIzaSyD53jSGvnzDRwQxzHGIu6viFmGjFGzIQXA");  
		$client->setClientId('522850139708-ke5b3r8m9sqtt1fbhr6aleg1d5m5hso9.apps.googleusercontent.com');
		$client->setClientSecret('ND8xIzcEL3CNcHZsHeqM9zqM');
		$client->setRedirectUri($scriptUri);

		$client->setScopes(array('https://www.googleapis.com/auth/calendar'));

		//Get user's oauth details and add them to an array to send request to Google
		$token_access = $row['access_token'];
		$token_type = $row['token_type'];
		$token_expire = $row['expires_in'];
		$token_refresh = $row['refresh_token'];
		$token_created = $row['created'];
		$token_user = $row['staff_id'];

		$array = array('access_token'=>$token_access, 'token_type'=>$token_type, 'expires_in'=>$token_expire, 'refresh_token'=>$token_refresh, 'created'=>$token_created);

		$get_staff_location = "SELECT room_number FROM staff WHERE staff_id = '$token_user'";
		$location_query = mysqli_query($connect, $get_staff_location);

		if(mysqli_num_rows($location_query) !== 0) {
			$row = mysqli_fetch_assoc($location_query);
			$location = $row['room_number'];
		} else {
			$location = NULL;
		}

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

		//Get a list of the user's Google calendar events
		$params = array('singleEvents' => 'true', 'showDeleted' => 'true', 'orderBy' => 'startTime');
		try {
			$events = $service->events->listEvents('primary', $params);
		} catch (Google_Service_Exception $e) {
			echo "An error has occurred with a Google Calendar request. Please return to the homepage. <br><br> <a href='/project/index.php'>Return Home</a>";
		}

		$rightNow = date('c');

		while(true) {
		  foreach ($events->getItems() as $event) {

		  	//only events that have not been cancelled and occur from today onwards should be added to the database
		  	if(strpos(strtolower($event->getStatus()), 'cancelled') === false && $event->start->dateTime >= $rightNow) {

		  		//only add events that specify free time 
			  	if (strpos(strtolower($event->getSummary()), 'free') !== false) {
			  		//Get the ID of the Google event
			  		$gcal_id = $event->getId();
				  	$dt = new DateTime($event->start->dateTime);

				  	$staff = $token_user;
					
					//get the date and times of the event
					$date = $dt->format('Y-m-d');
				  	$t1 = strtotime($event->start->dateTime);
				  	$t2 = strtotime($event->end->dateTime);
				 
				  	//While the start time of the Google event is less than the end time, add new 30 min slot
				  	while($t1 < $t2) {
				  		//get start time and end time of 30 minute slot
				  		$start = $t1;
				  		$end = strtotime('+30 minutes', $t1);

				  		//if slot is less than 30 minutes, apply event end time to $end instead of adding 30 minutes
				  		if($end > $t2) {
				  			$end = $t2;
				  		}

				  		//convert times to readable times and insert to database
				  		$start = date('H:i:s', $start);
				  		$end = date('H:i:s', $end);

				  		//check if an event is scheduled for the same time for this user
				  		$check_events = "SELECT event_date, start_time, end_time FROM events WHERE staff_id = '$staff' AND event_date = '$date' AND ((start_time <= '$start' AND end_time > '$start') OR (start_time < '$end' AND end_time >= '$end'))";
						$event_check = mysqli_query($connect, $check_events);

						//if there is no results from the query then insert new event to database
						if(mysqli_num_rows($event_check) === 0) {
							$add_event = "INSERT INTO events (staff_id, event_date, start_time, end_time, location, cal_event_id, status) VALUES ('$staff', '$date', '$start', '$end', '$location', '$gcal_id', 0)";
							$query = mysqli_query($connect, $add_event);
						}

						//add 30 minutes to the start time for next while loop
						$t1 = strtotime('+30 minutes', $t1);
				  	}
				}
		  	} else if(strpos(strtolower($event->getStatus()), 'cancelled') !== false) {
		  		$gcal_id = $event->getId();

		  		$check_events = "SELECT * FROM events WHERE cal_event_id = '$gcal_id'";
				$event_check = mysqli_query($connect, $check_events);

				//if there are results from the query then delete event from database
				if(mysqli_num_rows($event_check) !== 0) {
					$delete_event = "DELETE FROM events WHERE cal_event_id = '$gcal_id'";
					$query = mysqli_query($connect, $delete_event);

					//Check if a student is scheduled on the deleted event
					$row = mysqli_fetch_assoc($event_check);
					if(isset($row['student_id'])) {
						$student = $row['student_id'];
					}

					//Delete event within student's google calendar
					if(isset($student)) {
						
						//Get student's oauth details
						$sql = "SELECT * FROM oauth_token WHERE student_id = '$student'";
			
						$query = mysqli_query($connect, $sql);

						// Run a quick check to verify if this user has their gcal linked
						$quick_check = mysqli_num_rows($query);

						if($quick_check !== 0) {

							$scriptUri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
							$client = new Google_Client();
							$client->setApplicationName("UUJ Electronic Noticeboard");
							$client->setDeveloperKey("AIzaSyD53jSGvnzDRwQxzHGIu6viFmGjFGzIQXA");  
							$client->setClientId('522850139708-ke5b3r8m9sqtt1fbhr6aleg1d5m5hso9.apps.googleusercontent.com');
							$client->setClientSecret('ND8xIzcEL3CNcHZsHeqM9zqM');
							$client->setRedirectUri($scriptUri);

							$client->setScopes(array('https://www.googleapis.com/auth/calendar'));

							//Get user's oauth details and add them to an array to send request to Google
							$row = mysqli_fetch_assoc($query);
							$token_access = $row['access_token'];
							$token_type = $row['token_type'];
							$token_expire = $row['expires_in'];
							$token_refresh = $row['refresh_token'];
							$token_created = $row['created'];
							$token_user = $row['student_id'];
							
							$array = array('access_token'=>$token_access, 'token_type'=>$token_type, 'expires_in'=>$token_expire, 'refresh_token'=>$token_refresh, 'created'=>$token_created);

							//If user's access token is expired, retrieve a new access token and update both the database record and array
							if($client->isAccessTokenExpired()) {
								$client->refreshToken($token_refresh);
						        $newtoken = $client->getAccessToken();
						        $token = json_decode($newtoken ,true);
								$token_access = $token['access_token'];
						        $tokenupdate = "UPDATE oauth_token SET access_token = '$token_access' WHERE refresh_token = '$token_refresh' AND student_id = '$token_user'";
						        mysqli_query($connect, $tokenupdate);

						        $replacement = array('access_token'=>$token_access);
						        $array = array_replace($array, $replacement);
							}

							//Encode the array to a json array and gain access to the user's Google calendar
							$token = json_encode($array);
							$client->setAccessToken($token);
							$service = new Google_Service_Calendar($client);

							//delete the Google event from the student's Google calendar
							try {
								$service->events->delete('primary', $gcal_id);
							} catch (Google_Service_Exception $e) {
								echo "An error has occurred with a Google Calendar request. Please return to the homepage. <br><br> <a href='/project/index.php'>Return Home</a>";
							}
						}
					}
				}
		  	}
		  }

		  //Get the next page of results
		  $pageToken = $events->getNextPageToken();
		  if ($pageToken) {
		    $optParams = array('pageToken' => $pageToken, 'singleEvents' => 'true', 'showDeleted' => 'true', 'orderBy' => 'startTime');
		    
		    try {
				$events = $service->events->listEvents('primary', $optParams);
			} catch (Google_Service_Exception $e) {
				echo "An error has occurred with a Google Calendar request. Please return to the homepage. <br><br> <a href='/project/index.php'>Return Home</a>";											
				break;
			}
		  } else {
		    break;
		  }
		}
	}
}

?>