<?php

//allow access to regular users
$access = 'allow';

require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/project/Google/autoload.php');

//Only students have access to this file
if(strpos($user_check, "b00") !== false) {

	$staff = mysqli_real_escape_string($connect, $_POST['staff']);
	$student = mysqli_real_escape_string($connect, $user_check);
	$title = mysqli_real_escape_string($connect, $_POST['title']);
	$date = mysqli_real_escape_string($connect, $_POST['date']);
	$start_time = mysqli_real_escape_string($connect, $_POST['start-time']);
	$end_time = mysqli_real_escape_string($connect, $_POST['end-time']);
	$des = mysqli_real_escape_string($connect, $_POST['description']);

	$parts = explode(" ", $staff);
	$surname = array_pop($parts);
	$firstname = implode(" ", $parts);

	$get_staff_id = "SELECT staff_id FROM staff WHERE first_name = '$firstname' AND surname = '$surname'";
	$staff_query = mysqli_query($connect, $get_staff_id);

	if(mysqli_num_rows($staff_query) !== 0) {
		$row = mysqli_fetch_assoc($staff_query);
		$staff = $row['staff_id'];
	}

	//format time to add to database
	$start_time = strtotime($start_time);
	$end_time = strtotime($end_time);
	$start_time = date('H:i:s', $start_time);
	$end_time = date('H:i:s', $end_time);

	if($staff && $student && $title && $des) {

		//format date to insert to mysql database
		list($day,$month,$year) = sscanf($date, "%d/%d/%d");
		if($day < 10) {
			$day = "0" . $day;
		}
		if($month < 10) {
			$month = "0" . $month;
		}
		$date = $year . '-' . $month . '-' . $day;

		//Check for conflicting events
		$check_events = "SELECT event_date, start_time, end_time FROM events WHERE student_id = '$student' AND event_date = '$date' AND ((start_time <= '$start_time' AND end_time > '$start_time') OR (start_time < '$end_time' AND end_time >= '$end_time'))";
		$event_check = mysqli_query($connect, $check_events);

		//if there are no existing slots for this time then update event record
		if(mysqli_num_rows($event_check) === 0) {
			
			//sql query to update event table on student selecting event
			$sql = "UPDATE events SET student_id = '$student', event_title = '$title', description = '$des', status = 1 WHERE staff_id = '$staff' AND event_date = '$date' AND start_time = '$start_time' AND end_time = '$end_time'";
			$query = mysqli_query($connect, $sql);
		} else {
			header('Location: http://'.$_SERVER['HTTP_HOST'].'/project/views/event_confirmation.php?status=Error&start=' . $_POST['start-time'] . '&end=' . $_POST['end-time'] . '&date=' . $date);
			exit;
		}


		if($query && !isset($_POST['edit'])) {

			$get_student_email = "SELECT first_name, surname, email FROM student WHERE student_id = '$student'";
			$get_staff_email = "SELECT first_name, surname, email FROM staff WHERE staff_id = '$staff'";

			$student_query = mysqli_query($connect, $get_student_email);
			$staff_query = mysqli_query($connect, $get_staff_email);

			if(mysqli_num_rows($student_query) !== 0) {
				$student_row = mysqli_fetch_assoc($student_query);
				$first_name = $student_row['first_name'];
				$surname = $student_row['surname'];
				$student_email = $student_row['email'];

				$email_to = $student_email;

				// the message
				$msg = "Hi " . $first_name . ", /n/n" . $title . " has been scheduled for " . $date . " between " . $start_time . " and " . $end_time;

				// use wordwrap() if lines are longer than 70 characters
				$msg = wordwrap($msg,70);

				// send email
				mail($email_to,"New Event",$msg);
			}

			if(mysqli_num_rows($staff_query) !== 0) {
				$staff_row = mysqli_fetch_assoc($staff_query);
				$first_name = $staff_row['first_name'];
				$surname = $staff_row['surname'];
				$staff_email = $staff_row['email'];

				$email_to = $staff_email;

				// the message
				$msg = "Hi " . $first_name . ", /n/n" . $title . " has been scheduled for " . $date . " between " . $start_time . " and " . $end_time;

				// use wordwrap() if lines are longer than 70 characters
				$msg = wordwrap($msg,70);

				// send email
				mail($email_to,"Student Scheduled Event",$msg);
			}


			$sql = "SELECT * FROM oauth_token WHERE staff_id = '$staff' OR student_id = '$student'";
			$query = mysqli_query($connect, $sql);

			// Run a quick check to verify there are any results
			$quick_check = mysqli_num_rows($query);

			if($quick_check !== 0) {

				while($row = mysqli_fetch_assoc($query)) {

					$scriptUri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

					$client = new Google_Client();
					$client->setApplicationName("UUJ Electronic Noticeboard");
					$client->setDeveloperKey("AIzaSyD53jSGvnzDRwQxzHGIu6viFmGjFGzIQXA");  
					$client->setClientId('522850139708-ke5b3r8m9sqtt1fbhr6aleg1d5m5hso9.apps.googleusercontent.com');
					$client->setClientSecret('ND8xIzcEL3CNcHZsHeqM9zqM');
					$client->setRedirectUri($scriptUri);

					$client->setScopes(array('https://www.googleapis.com/auth/calendar'));

					//Get user's oauth details and add them to an array to send request to Google
					if(!empty($row['staff_id'])) {
						$token_access = $row['access_token'];
						$token_type = $row['token_type'];
						$token_expire = $row['expires_in'];
						$token_refresh = $row['refresh_token'];
						$token_created = $row['created'];
						$token_user = $row['staff_id'];
					} else {
						$token_access = $row['access_token'];
						$token_type = $row['token_type'];
						$token_expire = $row['expires_in'];
						$token_refresh = $row['refresh_token'];
						$token_created = $row['created'];
						$token_user = $row['student_id'];
					}
					
					$array = array('access_token'=>$token_access, 'token_type'=>$token_type, 'expires_in'=>$token_expire, 'refresh_token'=>$token_refresh, 'created'=>$token_created);

					//If user's access token is expired, retrieve a new access token and update both the database record and array
					if($client->isAccessTokenExpired()) {
						$client->refreshToken($token_refresh);
				        $newtoken = $client->getAccessToken();
				        $token = json_decode($newtoken ,true);
						$token_access = $token['access_token'];
						
						if(strpos($token_user, 'b00') === false) {
							$tokenupdate = "UPDATE oauth_token SET access_token = '$token_access' WHERE refresh_token = '$token_refresh' AND staff_id = '$token_user'";
						} else {
							$tokenupdate = "UPDATE oauth_token SET access_token = '$token_access' WHERE refresh_token = '$token_refresh' AND student_id = '$token_user'";
						}
				        
				        mysqli_query($connect, $tokenupdate);

				        $replacement = array('access_token'=>$token_access);
				        $array = array_replace($array, $replacement);
					}

					//Encode the array to a json array and gain access to the user's Google calendar
					$token = json_encode($array);
					$client->setAccessToken($token);
					$service = new Google_Service_Calendar($client);

					if(strpos($token_user, 'b00') === false) {
						$name = mysqli_real_escape_string($connect, $_POST['student']);
					} else {
						$name = mysqli_real_escape_string($connect, $_POST['staff']);
					}

					$gcal_start_time = $date . 'T' . $start_time;
					$gcal_end_time = $date . 'T' . $end_time;

					$event = new Google_Service_Calendar_Event();
					if(isset($createdEvent)) {
						$event->setId($gcal_id);
					}
					$event->setSummary('Meeting with ' . $name);
					$start = new Google_Service_Calendar_EventDateTime();
					$start->setTimeZone('Europe/London');
					$start->setDateTime($gcal_start_time);
					$event->setStart($start);
					$end = new Google_Service_Calendar_EventDateTime();
					$end->setTimeZone('Europe/London');
					$end->setDateTime($gcal_end_time);
					$event->setEnd($end);

					try {
						$createdEvent = $service->events->insert('primary', $event);
					} catch (Google_Service_Exception $e) {
						echo "An error has occurred with a Google Calendar request. Please return to the homepage. <br><br> <a href='/project/index.php'>Return Home</a>";
					}

					if(isset($createdEvent)) {
						$gcal_id = $createdEvent->getId();
					}
				}

				if(isset($createdEvent)) {
					$sql = "UPDATE events SET cal_event_id = '$gcal_id' WHERE staff_id = '$staff' AND event_date = '$date' AND start_time = '$start_time' AND end_time = '$end_time'";
					$query = mysqli_query($connect, $sql);
				}
			}

		} else {
			echo("Error: " . mysqli_error($connect));
		}

	} else {
		echo "All required details not submitted.";
	}

	if(!isset($_POST['edit'])) {
		//set status to creating event
		header('Location: http://'.$_SERVER['HTTP_HOST'].'/project/views/event_confirmation.php?status=Created&staff=' . $_POST['staff'] . "&start=" . $_POST['start-time'] . "&end=" . $_POST['end-time'] . "&date=" . $date);
	} else {
		//set status to editing event
		header('Location: http://'.$_SERVER['HTTP_HOST'].'/project/views/event_confirmation.php?status=Edited&staff=' . $_POST['staff'] . "&start=" . $_POST['start-time'] . "&end=" . $_POST['end-time'] . "&date=" . $date);
	}
}

?>