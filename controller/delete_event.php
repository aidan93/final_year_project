<?php 

include($_SERVER['DOCUMENT_ROOT'].'/project/controller/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/project/Google/autoload.php');

$user = mysqli_real_escape_string($connect, $_POST['user']);
$date = mysqli_real_escape_string($connect, $_POST['date']);
$selected_start_init = mysqli_real_escape_string($connect, $_POST['selected_start']);
$selected_end_init = mysqli_real_escape_string($connect, $_POST['selected_end']);

if($selected_start_init && $selected_end_init) {

	//format time to delete event from Google Cal and Database
	$start_time = strtotime($selected_start_init);
	$end_time = strtotime($selected_end_init);
	$start_time = date('H:i:s', $start_time);
	$end_time = date('H:i:s', $end_time);

	//if event is being deleted from student page then get staff id from database to delete event from staff Google Cal
	if(strpos($user, "b00") !== false) {
		$sql = "SELECT staff_id, cal_event_id FROM events WHERE student_id = '$user' AND event_date = '$date' AND start_time = '$start_time' AND end_time = '$end_time'";
		$gcalid_query = mysqli_query($connect, $sql);

		if(mysqli_num_rows($gcalid_query) > 0) {
			$row = mysqli_fetch_assoc($gcalid_query);
			$staff_id = $row['staff_id'];
			$gcal_id = $row['cal_event_id'];
		}
	} else {
		$sql = "SELECT student_id, cal_event_id FROM events WHERE staff_id = '$user' AND event_date = '$date' AND start_time = '$start_time' AND end_time = '$end_time'";
		$gcalid_query = mysqli_query($connect, $sql);

		if(mysqli_num_rows($gcalid_query) > 0) {
			$row = mysqli_fetch_assoc($gcalid_query);
			$gcal_id = $row['cal_event_id'];
			$student = $row['student_id'];
		}
	}

	if(strpos($user, "b00") !== false) {
		$sql = "DELETE FROM events WHERE staff_id = '$staff_id' AND event_date = '$date' AND start_time = '$start_time' AND end_time = '$end_time'";
	} else {
		$sql = "DELETE FROM events WHERE staff_id = '$user' AND event_date = '$date' AND start_time = '$start_time' AND end_time = '$end_time'";
	}

	$delete_query = mysqli_query($connect, $sql) or die (mysqli_error($connect));

	//if delete query successful then delete from google calendar as well
	if($delete_query) {
		
		$scriptUri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

		$client = new Google_Client();
		$client->setApplicationName("UUJ Electronic Noticeboard");
		$client->setDeveloperKey("AIzaSyD53jSGvnzDRwQxzHGIu6viFmGjFGzIQXA");  
		$client->setClientId('522850139708-ke5b3r8m9sqtt1fbhr6aleg1d5m5hso9.apps.googleusercontent.com');
		$client->setClientSecret('ND8xIzcEL3CNcHZsHeqM9zqM');
		$client->setRedirectUri($scriptUri);

		$client->setScopes(array('https://www.googleapis.com/auth/calendar'));

		if(strpos($user, "b00") !== false) {
			$sql = "SELECT * FROM oauth_token WHERE staff_id = '$staff_id'";
		} else {
			$sql = "SELECT * FROM oauth_token WHERE staff_id = '$user'";
		}
		
		$query = mysqli_query($connect, $sql) or die (mysqli_error($connect));

		// Run a quick check to verify there are any results
		$quick_check = mysqli_num_rows($query);

		if($quick_check !== 0) {
			$row = mysqli_fetch_assoc($query);
			$token_access = $row['access_token'];
			$token_type = $row['token_type'];
			$token_expire = $row['expires_in'];
			$token_refresh = $row['refresh_token'];
			$token_created = $row['created'];
			$token_user = $row['staff_id'];
			
			$array = array('access_token'=>$token_access, 'token_type'=>$token_type, 'expires_in'=>$token_expire, 'refresh_token'=>$token_refresh, 'created'=>$token_created);

			if($client->isAccessTokenExpired()) {
				$client->refreshToken($token_refresh);
		        $newtoken = $client->getAccessToken();
		        $token = json_decode($newtoken ,true);
				$token_access = $token['access_token'];
		        $tokenupdate = "UPDATE oauth_token SET access_token = '$token_access' WHERE refresh_token = '$token_refresh' AND staff_id = '$token_user'";
		        mysqli_query($connect, $tokenupdate) or die (mysqli_error($connect));

		        $replacement = array('access_token'=>$token_access);
		        $array = array_replace($array, $replacement);
			}

			$token = json_encode($array);
			$client->setAccessToken($token);

			$service = new Google_Service_Calendar($client);

			$sql = "SELECT event_id FROM events WHERE cal_event_id = '$gcal_id'";
			$gcal_query = mysqli_query($connect, $sql);

			if(mysqli_num_rows($gcal_query) > 1) {

				$event = $service->events->get('primary', $gcal_id);
				$gcal_start = $event->getStart()->getDateTime();
				$gcal_end = $event->getEnd()->getDateTime();

				$gcal_start_time = strtotime($gcal_start);
				$gcal_end_time = strtotime($gcal_end);

				$gcal_start_time = date('H:i:s', $gcal_start_time);
				$gcal_end_time = date('H:i:s', $gcal_end_time);

				//if slot is at the beginning of the free time event then make start time of free time event the end time of slot
				if($start_time === $gcal_start_time) {
					$start = strtotime('+30 minutes', strtotime($start_time));
					$start = date('H:i:s', $start);
					$gcal_start_time = $date . 'T' . $start;

					$start = new Google_Service_Calendar_EventDateTime();
					$start->setTimeZone('Europe/London');
					$start->setDateTime($gcal_start_time);
					$event->setStart($start);

					try {
						$updatedEvent = $service->events->update('primary', $event->getId(), $event);
					} catch (Google_Service_Exception $e) {
						echo "An error has occurred with a Google Calendar request. Please return to the homepage. <br><br> <a href='/project/index.php'>Return Home</a>";
					}
					
				} else if($end_time === $gcal_end_time) { 
					//if slot is at the end of the free time event then make end time of free time event the start time of slot
					$end = strtotime('-30 minutes', strtotime($end_time));
					$end = date('H:i:s', $end);
					$gcal_end_time = $date . 'T' . $end;

					$end = new Google_Service_Calendar_EventDateTime();
					$end->setTimeZone('Europe/London');
					$end->setDateTime($gcal_end_time);
					$event->setEnd($end);

					try {
						$updatedEvent = $service->events->update('primary', $event->getId(), $event);
					} catch (Google_Service_Exception $e) {
						echo "An error has occurred with a Google Calendar request. Please return to the homepage. <br><br> <a href='/project/index.php'>Return Home</a>";
					}

				} else {
					/*
					** if slot is in the middle of the free time slot then make start time of free time event the end time of that slot
					** then create a new free time event that starts at the initial free time event time and ends at this slots start time
					*/
					$gcal_start_time = $date . 'T' . $end_time;
					$title = $event->getSummary();

					$start = new Google_Service_Calendar_EventDateTime();
					$start->setTimeZone('Europe/London');
					$start->setDateTime($gcal_start_time);
					$event->setStart($start);

					try {
						$updatedEvent = $service->events->update('primary', $event->getId(), $event);
					} catch (Google_Service_Exception $e) {
						echo "An error has occurred with a Google Calendar request. Please return to the homepage. <br><br> <a href='/project/index.php'>Return Home</a>";
					}

					$new_gcal_end_time = $date . 'T' . $start_time;

					$event = new Google_Service_Calendar_Event();
					$event->setSummary($title);
					$start = new Google_Service_Calendar_EventDateTime();
					$start->setTimeZone('Europe/London');
					$start->setDateTime($gcal_start);
					$event->setStart($start);
					$end = new Google_Service_Calendar_EventDateTime();
					$end->setTimeZone('Europe/London');
					$end->setDateTime($new_gcal_end_time);
					$event->setEnd($end);

					try {
						$createdEvent = $service->events->insert('primary', $event);
					} catch (Google_Service_Exception $e) {
						echo "An error has occurred with a Google Calendar request. Please return to the homepage. <br><br> <a href='/project/index.php'>Return Home</a>";
					}

					if(isset($createdEvent)) {
						$gcal_id = $createdEvent->getId();

						$update_events = "UPDATE events SET cal_event_id = '$gcal_id' WHERE staff_id = '$token_user' AND event_date = '$date' AND start_time <= '$gcal_start_time' AND end_time >= '$end_time'";
						$update = mysqli_query($connect, $update_events) or die (mysqli_error($connect));
					}

				}

			} else {

				try {
					$service->events->delete('primary', $gcal_id);
				} catch (Google_Service_Exception $e) {
					echo "An error has occurred with a Google Calendar request. Please return to the homepage. <br><br> <a href='/project/index.php'>Return Home</a>";
				}

				//Get student ID from event if it exists
				if(strpos($user, "b00") !== false) {
					$student = $user;
				}

				//Delay event within student's google calendar
				if(isset($student)) {
					
					$sql = "SELECT * FROM oauth_token WHERE student_id = '$student'";
		
					$query = mysqli_query($connect, $sql) or die (mysqli_error($connect));

					// Run a quick check to verify if this user has their gcal linked
					$quick_check = mysqli_num_rows($query);

					if($quick_check !== 0) {

						$row = mysqli_fetch_assoc($query);
						$token_access = $row['access_token'];
						$token_type = $row['token_type'];
						$token_expire = $row['expires_in'];
						$token_refresh = $row['refresh_token'];
						$token_created = $row['created'];
						$token_user = $row['student_id'];
						
						$array = array('access_token'=>$token_access, 'token_type'=>$token_type, 'expires_in'=>$token_expire, 'refresh_token'=>$token_refresh, 'created'=>$token_created);

						if($client->isAccessTokenExpired()) {
							$client->refreshToken($token_refresh);
					        $newtoken = $client->getAccessToken();
					        $token = json_decode($newtoken ,true);
							$token_access = $token['access_token'];
					        $tokenupdate = "UPDATE oauth_token SET access_token = '$token_access' WHERE refresh_token = '$token_refresh' AND student_id = '$token_user'";
					        mysqli_query($connect, $tokenupdate) or die (mysqli_error($connect));

					        $replacement = array('access_token'=>$token_access);
					        $array = array_replace($array, $replacement);
						}

						$token = json_encode($array);
						$client->setAccessToken($token);

						$service = new Google_Service_Calendar($client);

						try {
							$service->events->delete('primary', $gcal_id);
						} catch (Google_Service_Exception $e) {
							echo "An error has occurred with a Google Calendar request. Please return to the homepage. <br><br> <a href='/project/index.php'>Return Home</a>";
						}
					}
				}
			}
				
			
			echo "/project/views/event_confirmation.php?status=Cancelled&date=" . $date . "&start=" . $start_time . "&end=" . $end_time;
		}

	}
}

?>