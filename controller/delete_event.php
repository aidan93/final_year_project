<?php 

//allow access to regular users
$access = 'allow';

require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/session.php');
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

	//Get number of events with same gcal_id
	$sql = "SELECT event_id FROM events WHERE cal_event_id = '$gcal_id'";
	$gcal_query = mysqli_query($connect, $sql);


	if(strpos($user, "b00") !== false) {
		$sql = "DELETE FROM events WHERE student_id = '$user' AND event_date = '$date' AND start_time = '$start_time' AND end_time = '$end_time'";
	} else {
		$sql = "DELETE FROM events WHERE staff_id = '$user' AND event_date = '$date' AND start_time = '$start_time' AND end_time = '$end_time'";
	}

	$delete_query = mysqli_query($connect, $sql);

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
			$sql = "SELECT * FROM oauth_token WHERE student_id = '$user'";
		} else {
			$sql = "SELECT * FROM oauth_token WHERE staff_id = '$user'";
		}
		
		$query = mysqli_query($connect, $sql);

		// Run a quick check to verify there are any results
		$quick_check = mysqli_num_rows($query);

		if($quick_check !== 0) {
			$row = mysqli_fetch_assoc($query);
			$token_access = $row['access_token'];
			$token_type = $row['token_type'];
			$token_expire = $row['expires_in'];
			$token_refresh = $row['refresh_token'];
			$token_created = $row['created'];

			if(strpos($user, "b00") !== false) {
				$token_user = $row['student_id'];
			} else {
				$token_user = $row['staff_id'];
			}
			
			
			$array = array('access_token'=>$token_access, 'token_type'=>$token_type, 'expires_in'=>$token_expire, 'refresh_token'=>$token_refresh, 'created'=>$token_created);

			if($client->isAccessTokenExpired()) {
				$client->refreshToken($token_refresh);
		        $newtoken = $client->getAccessToken();
		        $token = json_decode($newtoken ,true);
				$token_access = $token['access_token'];

				if(strpos($token_user, "b00") !== false) {
					$tokenupdate = "UPDATE oauth_token SET access_token = '$token_access' WHERE refresh_token = '$token_refresh' AND student_id = '$token_user'";
				} else {
					$tokenupdate = "UPDATE oauth_token SET access_token = '$token_access' WHERE refresh_token = '$token_refresh' AND staff_id = '$token_user'";
				}
		        mysqli_query($connect, $tokenupdate);

		        $replacement = array('access_token'=>$token_access);
		        $array = array_replace($array, $replacement);
			}

			$token = json_encode($array);
			$client->setAccessToken($token);

			$service = new Google_Service_Calendar($client);

			//If the number of events with the same gcal_id is greater than 1 then the Google event will need manipulated to remove this 30-minute interval
			if(mysqli_num_rows($gcal_query) > 1 && strpos($token_user, 'b00') === false) {

				$event = $service->events->get('primary', $gcal_id);
				$gcal_start = $event->getStart()->getDateTime();
				$gcal_end = $event->getEnd()->getDateTime();

				$gcal_start_time = strtotime($gcal_start);
				$gcal_end_time = strtotime($gcal_end);

				$gcal_start_time = date('H:i:s', $gcal_start_time);
				$gcal_end_time = date('H:i:s', $gcal_end_time);

				//if slot is at the beginning of the free time event then make start time of the Google event the end time of deleted event
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
						echo "/project/views/event_confirmation.php?status=Google";
						exit;
					}
					
				} else if($end_time === $gcal_end_time) { 
					//if slot is at the end of the Google event then make end time of the Google event the start time of deleted event
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
						echo "/project/views/event_confirmation.php?status=Google";
						exit;
					}

				} else {
					/*
					** if slot is in the middle of the Google event then make start time of Google event the end time of the deleted event
					** then create a new Google event that starts at the initial Google event time and ends at the deleted event's start time
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
						echo "/project/views/event_confirmation.php?status=Google";
						exit;
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
						echo "/project/views/event_confirmation.php?status=Google";
						exit;
					}

					if(isset($createdEvent)) {
						$gcal_id = $createdEvent->getId();

						$update_events = "UPDATE events SET cal_event_id = '$gcal_id' WHERE staff_id = '$token_user' AND event_date = '$date' AND start_time <= '$gcal_start_time' AND end_time >= '$end_time'";
						$update = mysqli_query($connect, $update_events);
					}

				}

			} else if(mysqli_num_rows($gcal_query) === 1) { //If the gcal_id of the deleted event is unique, remove the complete event from Google calendar 

				try {
					$service->events->delete('primary', $gcal_id);
				} catch (Google_Service_Exception $e) {
					echo "/project/views/event_confirmation.php?status=Google";
					exit;
				}

				//Delay event within other event participant's google calendar
				if(isset($student) || isset($staff_id)) {
					
					if(isset($student)) {
						$sql = "SELECT * FROM oauth_token WHERE student_id = '$student'";
					} else {
						$sql = "SELECT * FROM oauth_token WHERE staff_id = '$staff_id'";
					}
		
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
						
						//Get relevant user's ID
						if(!empty($row['staff_id'])) {
							$token_user = $row['staff_id'];
						} else if(!empty($row['student_id'])) {
							$token_user = $row['student_id'];
						}
						
						$array = array('access_token'=>$token_access, 'token_type'=>$token_type, 'expires_in'=>$token_expire, 'refresh_token'=>$token_refresh, 'created'=>$token_created);

						//If user's access token is expired, retrieve a new access token and update both the database record and array
						if($client->isAccessTokenExpired()) {
							$client->refreshToken($token_refresh);
					        $newtoken = $client->getAccessToken();
					        $token = json_decode($newtoken ,true);
							$token_access = $token['access_token'];
					        
							if(!empty($row['staff_id'])) {
								$tokenupdate = "UPDATE oauth_token SET access_token = '$token_access' WHERE refresh_token = '$token_refresh' AND staff_id = '$token_user'";
							} else if(!empty($row['student_id'])) {
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

						//Delete event in user's Google calendar
						try {
							$service->events->delete('primary', $gcal_id);
						} catch (Google_Service_Exception $e) {
							echo "/project/views/event_confirmation.php?status=Google";
							exit;
						}
					}
				}
			}
		}

		echo "/project/views/event_confirmation.php?status=Cancelled&date=" . $date . "&start=" . $start_time . "&end=" . $end_time;
	}
}

?>