<?php

//allow access to regular users
$access = 'allow';

require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/session.php');

$user = mysqli_real_escape_string($connect, $_POST['user']);
$post = mysqli_real_escape_string($connect, $_POST['post']);

if("" !== trim($post) && "" !== trim($user)) {
	$sql = "INSERT INTO posts (post_id, staff_id, time_created, text) VALUES (NULL, '$user', NULL, '$post')";
	$query = mysqli_query($connect, $sql) or die (mysqli_error($connect));

	if($query) {
		header("location: http://".$_SERVER['HTTP_HOST']."/project/views/profile.php?user=" . $user);
		exit;
	} else {
		header("location: http://".$_SERVER['HTTP_HOST']."/project/views/profile.php?error=insert&user=" . $user);
		exit;
	}
}

?>