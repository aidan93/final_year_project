<?php

//restrict access to regular users
$access = 'restricted';

require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/session.php');

if(isset($_GET['status'])) {
	$status = $_GET['status'];
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Admin Profile</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,700,400' rel='stylesheet' type='text/css'>
	<link href="/project/style/style.css" rel="stylesheet" type="text/css">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js"></script>
	<script type="text/javascript" src="/project/js/admin.js"></script>
</head>
<body>
	<div class="overlay"></div>
	<div id="header">
		<div id="profile">
			<span id="welcome"><?php echo $login_session; ?></span>
			<b id="logout"><a href="/project/views/logout.php">Log Out</a></b>
		</div>
		<a href="/project/index.php" id="logo"><img src="/project/images/logo.png" /></a>
	</div>
	<div class="wrapper">
		<div id="admin_controls">
			<div class="controls">
				<h3>Admin Controls</h3>
				<div class="admin_buttons">
					<button type="button" id="add_staff">Create New Staff Member</button>
					<button type="button" id="add_student">Create New Student</button>
					<button type="button" class="edit">Edit/Delete User</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Popup box that appears to create staff member -->
	<div id="staff_popup">
		<h3 id="popup-header">Create New Staff Member</h3>
		<a href="#" class="close-popup"><img src="/project/images/close-icon.png" /></a>

		<form action='/project/controller/admin_add_user.php' method='post'>
		<li class='form_row'><label for='id' class='form_title'>Staff ID:</label><input type='text' name='id' required></li>
		<li class='form_row'><label for='first_name' class='form_title'>First Name:</label><input type='text' name='first_name' required></li>
		<li class='form_row'><label for='surname' class='form_title'>Surname:</label><input type='text' name='surname' required></li>
		<li class='form_row'><label for='room' class='form_title'>Room:</label><input type='text' name='room' required></li>
		<li class='form_row'><label for='email' class='form_title'>Email: </label><input type='email' name='email' required></li>
		<li class='form_row'><label for='password' class='form_title'>Password:</label><input type='password' name='password' required></li>
		
		<input type='submit'></form>
	</div>

	<!-- Popup box that appears to create student -->
	<div id="student_popup">
		<h3 id="popup-header">Create New Student</h3>
		<a href="#" class="close-popup"><img src="/project/images/close-icon.png" /></a>

		<form action='/project/controller/admin_add_user.php' method='post'>
		<li class='form_row'><label for='id' class='form_title'>Student ID:</label><input type='text' name='id' required></li>
		<li class='form_row'><label for='first_name' class='form_title'>First Name:</label><input type='text' name='first_name' required></li>
		<li class='form_row'><label for='surname' class='form_title'>Surname:</label><input type='text' name='surname' required></li>
		<li class='form_row'><label for='course' class='form_title'>Course:</label><input type='text' name='course' required></li>
		<li class='form_row'><label for='email' class='form_title'>Email: </label><input type='email' name='email' required></li>
		<li class='form_row'><label for='password' class='form_title'>Password:</label><input type='password' name='password' required></li>
		
		<input type='submit'></form>
	</div>

	<div id="search-popup">
		<a href="#" class="close-popup"><img src="/project/images/close-icon.png" /></a>
		<div id="searchform"> 
		    <input type="text" id="search_text" name="search_name" placeholder="Search User"> 
		    <button type="submit" id="search_button"></button> 
		</div> 
	</div>

	<div id="popup">
		<a href="#" class="close-popup"><img src="/project/images/close-icon.png" /></a>
	</div>
</body>