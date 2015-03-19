<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once($_SERVER['DOCUMENT_ROOT'].'/project/Google/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/connect.php');

$scriptUri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

$client = new Google_Client();
$client->setApplicationName("UUJ Electronic Noticeboard");
$client->setDeveloperKey("AIzaSyD53jSGvnzDRwQxzHGIu6viFmGjFGzIQXA");  
$client->setClientId('522850139708-ke5b3r8m9sqtt1fbhr6aleg1d5m5hso9.apps.googleusercontent.com');
$client->setClientSecret('ND8xIzcEL3CNcHZsHeqM9zqM');
$client->setRedirectUri($scriptUri);

$client->setScopes(array('https://www.googleapis.com/auth/calendar'));

$sql = "SELECT * FROM oauth_token";
$query = mysqli_query($connect, $sql) or die (mysqli_error($connect));

// Run a quick check to verify there are any results
$quick_check = mysqli_num_rows($query);

if($quick_check !== 0) {

	while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		$token_access = $row['access_token'];
		$token_type = $row['token_type'];
		$token_expire = $row['expires_in'];
		$token_refresh = $row['refresh_token'];
		$token_created = $row['created'];
		$token_user = $row['staff_id'];

		$array = array('access_token'=>$token_access, 'token_type'=>$token_type, 'expires_in'=>$token_expire, 'refresh_token'=>$token_refresh, 'created'=>$token_created);

		$get_staff_location = "SELECT room_number FROM staff WHERE staff_id = '$token_user'";
		$location_query = mysqli_query($connect, $get_staff_location) or die (mysqli_error($connect));

		if(mysqli_num_rows($location_query) !== 0) {
			$row = mysqli_fetch_assoc($location_query);
			$location = $row['room_number'];
		} else {
			$location = NULL;
		}

		if($client->isAccessTokenExpired()) {
			$client->refreshToken($token_refresh);
	        $newtoken = $client->getAccessToken();
	        $tokenupdate = "UPDATE oauth_token SET access_token = '$newtoken' WHERE refresh_token = '$token_refresh'";
	        mysqli_query($connect, $tokenupdate) or die (mysqli_error($connect));

	        $replacement = array('access_token'=>$newtoken);
	        $array = array_replace($array, $replacement);
		}

		$token = json_encode($array);
		$client->setAccessToken($token);

		$service = new Google_Service_Calendar($client);

		$rightNow = gmdate('c');

		$params = array('singleEvents' => 'true', 'orderBy' => 'startTime', 'timeMin' => $rightNow);
		$events = $service->events->listEvents('primary', $params);

		while(true) {
		  foreach ($events->getItems() as $event) {

		  	//only add any events that specify free time 
		  	if (strpos(strtolower($event->getSummary()), 'free') !== false) {
			  	$dt = new DateTime($event->start->dateTime);
			  	$dt_end = new DateTime($event->end->dateTime);

			  	$staff = $token_user;
				$date = $dt->format('Y-m-d');
				$start = $dt->format('H:i:s');
				$end = $dt_end->format('H:i:s');

				$check_events = "SELECT event_date, start_time, end_time FROM events WHERE staff_id = '$staff' AND event_date = '$date' AND start_time = '$start' AND end_time = '$end'";
				$event_check = mysqli_query($connect, $check_events) or die (mysqli_error($connect));
				if(mysqli_num_rows($event_check) === 0) {
					$add_event = "INSERT INTO events (staff_id, event_date, start_time, end_time, location, status) VALUES ('$staff', '$date', '$start', '$end', '$location', 0)";
					$query = mysqli_query($connect, $add_event) or die (mysqli_error($connect));
				}
			}
		  }
		  $pageToken = $events->getNextPageToken();
		  if ($pageToken) {
		    $optParams = array('pageToken' => $pageToken);
		    $events = $service->events->listEvents('primary', $optParams);
		  } else {
		    break;
		  }
		}
	}
}

?>