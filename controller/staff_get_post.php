<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/connect.php');

//get post_id from popup attribute
$post_id = mysqli_real_escape_string($connect, $_POST['post']);

//if post_id is not empty then get post information from db
if("" !== trim($post_id)) {
	$sql = "SELECT * FROM posts WHERE post_id = '$post_id'";
	$query = mysqli_query($connect, $sql) or die (mysqli_error($connect));

	if(mysqli_num_rows($query) > 0) {
		$row = mysqli_fetch_assoc($query);

		$user = $row['staff_id'];
		$text = $row['text'];

		$form = "<form action='/project/controller/staff_edit_post.php' method='post'>";
		$form .= "<input type='hidden' name='post-id' value='".$post_id."'>";
		$form .= "<input type='hidden' name='user' value='".$user."'>";
		$form .= "<textarea name='text' cols='40' rows='3' required>".$text."</textarea>";
		$form .= "<input type='submit' value='Confirm'></form>";

		echo $form;
	}
}

?>