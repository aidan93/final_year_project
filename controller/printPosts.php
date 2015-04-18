<?php 

require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/connect.php');

$sql = "SELECT * FROM posts WHERE staff_id = '$user_profile' ORDER BY time_created DESC LIMIT 5";
$query = mysqli_query($connect, $sql) or die (mysqli_error($connect));

if(mysqli_num_rows($query) > 0) {

	$posts = "";
	while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		$id = $row['post_id'];
		$text = $row['text'];

		$posts .= "<div class='post' data-post-id=".$id."><a href='#' class='post-icon'><img src='/project/images/post_dropdown.png'></a><p class='post-text'>".$text."</p></div>";
	}

	echo $posts;
} else {
	echo "<h4>No posts to display at this time.</h4>";
}

?>