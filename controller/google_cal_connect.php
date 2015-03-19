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
$client->setAccessType('offline');   // Gets us our refreshtoken

$client->setScopes(array('https://www.googleapis.com/auth/calendar'));

// if logged in user is staff then get access token from db (if exists)
if(isset($_SESSION['login_user']) && strpos($_SESSION['login_user'], "b00") === false) {
	
	// The user accepted your access now you need to exchange it.
	if (isset($_GET['code'])) {
		
		$credentials = $client->authenticate($_GET['code']);

		$token = json_decode($credentials ,true);
		$token_access = $token['access_token'];
		$token_type = $token['token_type'];
		$token_expire = $token['expires_in'];
		$token_refresh = $token['refresh_token'];
		$token_created = $token['created'];
		$token_user = $_SESSION['login_user'];

		$sql = "INSERT INTO oauth_token (access_token, token_type, expires_in, refresh_token, created, staff_id) VALUES ('$token_access', '$token_type', '$token_expire', '$token_refresh', '$token_created', '$token_user')";
		$query = mysqli_query($connect, $sql) or die (mysqli_error($connect));
		
		// $redirect = 'http://' . $_SERVER['HTTP_HOST'] . "/project/views/profile.php?user=" . $_SESSION['login_user'];
		// header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
	}

	$user = $_SESSION['login_user'];
	$sql = "SELECT staff_id FROM oauth_token WHERE staff_id = '$user'";
	$query = mysqli_query($connect, $sql) or die (mysqli_error($connect));
	$num_rows = mysqli_num_rows($query);

	// if token does not exist in db then set up link
	if($num_rows === 0) {
		$authUrl = $client->createAuthUrl();
		print "<a class='login' href='$authUrl'>Connect Me!</a>";
	}
}
 
?>