<?php

//allow access to regular users
$access = 'allow';

require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/session.php');

$user = mysqli_real_escape_string($connect, $_POST['user']);

//Check that the post contains text
if("" !== trim($_POST['post'])) {
	
	//Get post text
	$post = mysqli_real_escape_string($connect, $_POST['post']);

	//Insert post to database
	$sql = "INSERT INTO posts (post_id, staff_id, time_created, text) VALUES (NULL, '$user', NULL, '$post')";
	$query = mysqli_query($connect, $sql);

	//If inserting post was successful redirect back to user's profile page
	if($query) {
		header("location: http://".$_SERVER['HTTP_HOST']."/project/views/profile.php?user=" . $user);
		exit;
	} else {
		//if inserting post was unsuccessful display error message
		header("location: http://".$_SERVER['HTTP_HOST']."/project/views/profile.php?error=insert&user=" . $user);
		exit;
	}
} else {
	//if inserting post was unsuccessful display error message
	header("location: http://".$_SERVER['HTTP_HOST']."/project/views/profile.php?error=insert&user=" . $user);
	exit;
}

?>