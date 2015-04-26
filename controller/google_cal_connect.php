<?php 

require_once($_SERVER['DOCUMENT_ROOT'].'/project/Google/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/connect.php');

$scriptUri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

$client = new Google_Client();
$client->setApplicationName("UUJ Electronic Noticeboard");
$client->setDeveloperKey("AIzaSyD53jSGvnzDRwQxzHGIu6viFmGjFGzIQXA");  
$client->setClientId('522850139708-ke5b3r8m9sqtt1fbhr6aleg1d5m5hso9.apps.googleusercontent.com');
$client->setClientSecret('ND8xIzcEL3CNcHZsHeqM9zqM');
$client->setRedirectUri($scriptUri);
$client->setAccessType('offline');   // Gets us our refreshtoken

$client->setScopes(array('https://www.googleapis.com/auth/calendar'));

// make sure login_user is set in session then get access token from db (if exists)
if(isset($_SESSION['login_user'])) {
	
	// The user accepted your access now you need to exchange it.
	if(isset($_GET['code'])) {

		//if code is returned then amend url to contain user id
		header("location: http://".$_SERVER['HTTP_HOST']."/project/views/profile.php?user=" . $_SESSION['login_user'] . "&code=" . $_GET['code']);

		try {
			$credentials = $client->authenticate($_GET['code']);
		} catch (Google_Auth_Exception $e) {
			header("location: http://".$_SERVER['HTTP_HOST']."/project/views/profile.php?user=" . $_SESSION['login_user']);
		}
		
		// if code has not been used previously then authenticate and add to database
		if(isset($credentials)) {
			$token = json_decode($credentials ,true);
			$token_access = $token['access_token'];
			$token_type = $token['token_type'];
			$token_expire = $token['expires_in'];
			$token_refresh = $token['refresh_token'];
			$token_created = $token['created'];
			$token_user = $_SESSION['login_user'];

			if(strpos($_SESSION['login_user'], "b00") !== false) {
				$sql = "INSERT INTO oauth_token (access_token, token_type, expires_in, refresh_token, created, staff_id, student_id) VALUES ('$token_access', '$token_type', '$token_expire', '$token_refresh', '$token_created', NULL, '$token_user')";
			} else {
				$sql = "INSERT INTO oauth_token (access_token, token_type, expires_in, refresh_token, created, staff_id, student_id) VALUES ('$token_access', '$token_type', '$token_expire', '$token_refresh', '$token_created', '$token_user', NULL)";
			}
			
			$query = mysqli_query($connect, $sql);

			//get todays date and check if the user has any events scheduled for today or in the future
			$dateNow = date('Y-m-d');
			if(strpos($token_user, 'b00') !== false) {
				$upcoming_events = "SELECT * FROM events WHERE student_id = '$token_user' AND event_date >= '$dateNow' AND status = 1";
			} else {
				$upcoming_events = "SELECT * FROM events WHERE staff_id = '$token_user' AND event_date >= '$dateNow' AND status = 1";
			}
			
			$event_query = mysqli_query($connect, $upcoming_events);
			$quick_check = mysqli_num_rows($event_query);

			//check to make sure events exist
			if($quick_check !== 0) {
				$client->setAccessToken($credentials);
				$service = new Google_Service_Calendar($client);

				/* for each event get the other participant, date, start time and end time
				** then add that event to the user's google calendar
				*/
				while($row = mysqli_fetch_array($event_query, MYSQLI_ASSOC)) {
					
					//Get the name of the user the event is scheduled with
					if(strpos($token_user, 'b00') !== false) {
						$user = $row['staff_id'];
						$user_name = "SELECT first_name, surname FROM staff WHERE staff_id = '$user'";
					} else {
						$user = $row['student_id'];
						$user_name = "SELECT first_name, surname FROM student WHERE student_id = '$user'";
					}

					$user_query = mysqli_query($connect, $user_name);
					$check = mysqli_num_rows($user_query);

					//make sure the user exists before adding the name
					if($check !== 0) {
						$name = mysqli_fetch_assoc($user_query);
						$first_name = $name['first_name'];
						$surname = $name['surname'];

						$username = $first_name . ' ' . $surname;
					}

					//get the date, start time and end time of event
					$date = $row['event_date'];
					$start_time = $row['start_time'];
					$end_time = $row['end_time'];

					//Set the start time and end time of the google calendar event based on this event
					$gcal_start_time = $date . 'T' . $start_time;
					$gcal_end_time = $date . 'T' . $end_time;

					//set the relevant criteria for the new google calendar event
					$event = new Google_Service_Calendar_Event();
					//if this event has already been added to another google calendar account use the same id
					if(isset($row['cal_event_id'])) {
						$event->setId($row['cal_event_id']);
					}
					$event->setSummary('Meeting with ' . $username);
					$start = new Google_Service_Calendar_EventDateTime();
					$start->setTimeZone('Europe/London');
					$start->setDateTime($gcal_start_time);
					$event->setStart($start);
					$end = new Google_Service_Calendar_EventDateTime();
					$end->setTimeZone('Europe/London');
					$end->setDateTime($gcal_end_time);
					$event->setEnd($end);

					try {
						//add event to the user's google calendar
						$createdEvent = $service->events->insert('primary', $event);

						//if this event has not been added to another google calendar account then add this events id to the database
						if(!isset($row['cal_event_id'])) {
							$gcal_id = $createdEvent->getId();
						}
					} catch (Google_Service_Exception $e) {
						echo "An error has occurred with a Google Calendar request. Please return to the homepage. <br><br> <a href='/project/index.php'>Return Home</a>";
					}

					//apply created events id to the database for consistency
					if(isset($createdEvent) && isset($gcal_id)) {
						$sql = "UPDATE events SET cal_event_id = '$gcal_id' WHERE staff_id = '$staff' AND event_date = '$date' AND start_time = '$start_time' AND end_time = '$end_time'";
						$update_query = mysqli_query($connect, $sql);
					}
				}
			}

			header("location: http://".$_SERVER['HTTP_HOST']."/project/views/profile.php?user=" . $_SESSION['login_user']);
		}
	}

	//check if user has already set up their google calendar
	$user = $_SESSION['login_user'];
	$sql = "SELECT staff_id, student_id FROM oauth_token WHERE staff_id = '$user' OR student_id = '$user'";
	$query = mysqli_query($connect, $sql);
	$num_rows = mysqli_num_rows($query);

	// if token does not exist in db then set up link
	if($num_rows === 0) {
		$authUrl = $client->createAuthUrl();
		$google_button = "<a id='google_login' href='$authUrl'>Connect to Google Calendar</a>";
	}
}
 
?>