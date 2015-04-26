<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/connect.php');

//get post_id, user and text from submitted form
$post_id = mysqli_real_escape_string($connect, $_POST['post-id']);
$user = mysqli_real_escape_string($connect, $_POST['user']);
$text = mysqli_real_escape_string($connect, $_POST['text']);

//if post_id and text is not empty then edit post information in db
if("" !== trim($post_id) && "" !== trim($text)) {
	$sql = "UPDATE posts SET text = '$text' WHERE post_id = '$post_id'";
	$query = mysqli_query($connect, $sql);
	
	//If editing post was successful redirect back to user's profile page
	if($query) {
		header("location: http://".$_SERVER['HTTP_HOST']."/project/views/profile.php?user=" . $user);
		exit;
	} else {
		//if editing post was unsuccessful display error message
		header("location: http://".$_SERVER['HTTP_HOST']."/project/views/profile.php?error=edit&user=" . $user);
		exit;
	}
} else {
	//if editing post was unsuccessful display error message
	header("location: http://".$_SERVER['HTTP_HOST']."/project/views/profile.php?error=edit&user=" . $user);
	exit;
}

?>