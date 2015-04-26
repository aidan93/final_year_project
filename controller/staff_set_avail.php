<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/connect.php');

//get user and availability from ajax request
$user = mysqli_real_escape_string($connect, $_POST['user']);
$avail = mysqli_real_escape_string($connect, $_POST['avail']);

//if user and avail is not empty then change availability information in db
if("" !== trim($user) && "" !== trim($avail)) {
	$sql = "UPDATE staff SET availability = '$avail' WHERE staff_id = '$user'";
	$query = mysqli_query($connect, $sql);
}

?>