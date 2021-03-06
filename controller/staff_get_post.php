<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/connect.php');

//get post_id from popup attribute
$action = mysqli_real_escape_string($connect, $_POST['action']);
$post_id = mysqli_real_escape_string($connect, $_POST['post']);

//if post_id is not empty then get post information from db
if("" !== trim($post_id)) {
	$sql = "SELECT * FROM posts WHERE post_id = '$post_id'";
	$query = mysqli_query($connect, $sql);

	//If there is a post relating to the post ID, send form data back to Ajax request
	if(mysqli_num_rows($query) !== 0) {
		$row = mysqli_fetch_assoc($query);

		//Get staff ID and post text to insert to form
		$user = $row['staff_id'];
		$text = $row['text'];

		//convert time to display the day, month, year, hour and minute of post creation
		$time = date('r', strtotime($row['time_created']));
		$time = date('d/m/Y H:i', strtotime($time));

		//If action is to edit the post then send a form to the Ajax request
		if($action === 'edit') {
			$form = "<form action='/project/controller/staff_edit_post.php' method='post'>";
			$form .= "<input type='hidden' name='post-id' value='".$post_id."'>";
			$form .= "<input type='hidden' name='user' value='".$user."'>";
			$form .= "<textarea name='text' cols='40' rows='3' required>".$text."</textarea>";
			$form .= "<input type='submit' value='Confirm'></form>";
		} else if($action === 'view') {
			//If action is to view the post then send relevant information back to Ajax request
			$form = "<div id='view_post'><h4 id='time'>Created: ".$time."</h4><p id='post_text'>".$text."</p></div>";
		}

		echo $form;
	}
}

?>