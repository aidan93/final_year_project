<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/connect.php');

//get post_id from submitted form
$post_id = mysqli_real_escape_string($connect, $_POST['post']);
$user = mysqli_real_escape_string($connect, $_POST['user']);

//if post_id is not empty then delete post information in db
if("" !== trim($post_id)) {
	$sql = "DELETE FROM posts WHERE post_id = '$post_id'";
	$query = mysqli_query($connect, $sql);

	if($query) {
		echo "http://".$_SERVER['HTTP_HOST']."/project/views/profile.php?user=" . $user;
	} else {
		echo "http://".$_SERVER['HTTP_HOST']."/project/views/profile.php?error=delete&user=" . $user;
	}
}

?>