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
		$staff_query = mysqli_query($connect, $sql);

		if(mysqli_num_rows($staff_query) > 0) {
			$row = mysqli_fetch_assoc($staff_query);
			$staff_id = $row['staff_id'];
			$gcal_id = $row['cal_event_id'];
		}
	} else {
		$sql = "SELECT cal_event_id FROM events WHERE staff_id = '$user' AND event_date = '$date' AND start_time = '$start_time' AND end_time = '$end_time'";
		$gcalid_query = mysqli_query($connect, $sql);

		if(mysqli_num_rows($gcalid_query) > 0) {
			$row = mysqli_fetch_assoc($gcalid_query);
			$gcal_id = $row['cal_event_id'];
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
		        $tokenupdate = "UPDATE oauth_token SET access_token = '$newtoken' WHERE refresh_token = '$token_refresh' AND staff_id = '$token_user'";
		        mysqli_query($connect, $tokenupdate) or die (mysqli_error($connect));

		        $replacement = array('access_token'=>$newtoken);
		        $array = array_replace($array, $replacement);
			}

			$token = json_encode($array);
			$client->setAccessToken($token);

			$service = new Google_Service_Calendar($client);

			$service->events->delete('primary', $gcal_id);	
			
			echo "/project/views/event_confirmation.php?status=Cancelled&date=" . $date . "&start=" . $start_time . "&end=" . $end_time;
		}

	}
}

?>