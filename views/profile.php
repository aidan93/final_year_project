<?php

//allow access to regular users
$access = 'allow';

require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/calendar_setup.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/google_cal_connect.php');

//Get the user ID of profile
if(isset($_GET["user"])) {
	$user_profile = $_GET["user"];

	if(strpos($user_profile, "b00") !== false && strpos($_SESSION['login_user'], "b00") === false) {
		
		//if staff try to access student page send them back to their home page
		header("location: http://".$_SERVER['HTTP_HOST']."/project/views/profile.php?user=" . $_SESSION['login_user']);
	} else if(strpos($user_profile, "b00") !== false && strpos($_SESSION['login_user'], "b00") !== false && $_SESSION['login_user'] !== $user_profile) {
		
		//if student tries to access another student page send them back to their home page
		header("location: http://".$_SERVER['HTTP_HOST']."/project/views/profile.php?user=" . $_SESSION['login_user']);
	} else if(strpos($user_profile, "b00") === false && strpos($_SESSION['login_user'], "b00") === false && $_SESSION['login_user'] !== $user_profile) {
		
		//if staff member tries to access another staff member's page send them back to their home page
		header("location: http://".$_SERVER['HTTP_HOST']."/project/views/profile.php?user=" . $_SESSION['login_user']);
	} else {
		if(strpos($user_profile, "b00") !== false) {
			$sql = "SELECT first_name, surname FROM student WHERE student_id = '$user_profile'";
		} else {
			$sql = "SELECT first_name, surname, availability FROM staff WHERE staff_id = '$user_profile'";
		}

		$query = mysqli_query($connect, $sql);
		
		// Run a quick check to verify there are any results
		$quick_check = mysqli_num_rows($query);

		//If there is a result available, get the user's name and, if available, their availability
		if($quick_check !== 0) {
			$row = mysqli_fetch_assoc($query);
			$name = $row['first_name'] . " " . $row['surname'];
			
			if(isset($row['availability'])) {
				$avail = $row['availability'];
			}
		} else {
			//if user does not exist send them back to their home page
			header("location: http://".$_SERVER['HTTP_HOST']."/project/views/profile.php?user=" . $_SESSION['login_user']);
		}
	}
} else if(isset($_GET["code"]) && !isset($_GET["user"])) {
	//if code is set and user id is not then amend url to include user id
	header("location: http://".$_SERVER['HTTP_HOST']."/project/views/profile.php?user=" . $_SESSION['login_user'] . "&code=" . $_GET["code"]);
} else if(!isset($_GET["code"]) && !isset($_GET["user"])) {
	//if neither the google code or user id are present in the url then add the user id
	header("location: http://".$_SERVER['HTTP_HOST']."/project/views/profile.php?user=" . $_SESSION['login_user']);
}

?>
<!DOCTYPE html>
<html>
<head>
	<?php if($_SESSION['login_user'] === $user_profile) { ?>
		<title>Your Profile</title>
	<?php } else { ?>
		<title><?php echo $name ?>'s Profile</title>
	<?php } ?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,700,400' rel='stylesheet' type='text/css'>
	<link href="/project/style/style.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/themes/smoothness/jquery-ui.css" />

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js"></script>
	<script type="text/javascript" src="/project/js/jquery.dotdotdot.min.js"></script>
	<script type="text/javascript" src="/project/js/create_event.js"></script>
	<script type="text/javascript" src="/project/js/legend.js"></script>
	<!-- Only available for staff viewing their own profile -->
	<?php if($_SESSION['login_user'] === $user_profile && strpos($_SESSION['login_user'], "b00") === false) { ?>
		<script type="text/javascript" src="/project/js/availability.js"></script>
	<?php } ?>

	<script type="text/javascript" src="/project/js/post.js"></script>
</head>
<body>
	<div class="overlay"></div>
	<div id="header">
		<div id="profile">
			<span id="welcome"><?php echo $login_session; ?></span>
			<b id="logout"><a href="/project/views/logout.php">Log Out</a></b>
		</div>
		<a href="/project/index.php" id="logo"><img src="/project/images/logo.png" /></a>
		<?php if(strpos($_SESSION['login_user'], "b00") !== false) { ?>
			<form method="post" action="search.php" id="searchform"> 
			    <input type="text" name="search_name" placeholder="Search Staff"> 
			    <button type="submit"></button> 
			</form> 
		<?php } ?>
	</div>
	<div class="wrapper">
		<?php if($_SESSION['login_user'] === $user_profile) { ?>
			<h2 id="profile_header">Your Profile</h2>
		<?php } else { ?>
			<h2 id="profile_header"><?php echo $name ?>'s Profile</h2>
		<?php } ?>
		<?php if(strpos($user_profile, "b00") === false) { ?>
			<div id="availability">
				<!-- If logged in user is a staff member and viewing their own profile, make availability clickable to change -->
				<?php if($_SESSION['login_user'] === $user_profile && strpos($_SESSION['login_user'], "b00") === false) { 
						if($avail === '0') { ?>
							<a href="#"><span class='busy'>Currently Busy</span></a>
						<?php } else { ?>
							<a href="#"><span class='available'>Currently Available</span></a>
						<?php } ?>
				<?php } else {
						if($avail === '0') { ?>
							<span class='busy'>Currently Busy</span>
						<?php } else { ?>
							<span class='available'>Currently Available</span>
						<?php } ?>
				<?php } ?>
			</div>
		<?php } ?>
		<?php if(strpos($user_profile, "b00") !== false) { ?>
			<div class="table_wrapper student_cal">
		<?php } else { ?>
			<div class="table_wrapper staff_cal">
		<?php } ?>
		<?php 
			//user has to be accessing their own profile and not already have google calendar set up to see button
			if($_SESSION['login_user'] === $user_profile && $num_rows === 0) {
				echo $google_button;
			}
		?>
		<!-- Staff controls to add/edit events -->
		<?php if(strpos($_SESSION['login_user'], "b00") === false && $_SESSION['login_user'] === $user_profile) { ?>
			<div id="staff_controls">
				<button type="button" id="staff_add_event">Create New Event</button>
				<a href="/project/views/delay_view.php?user=<?php echo $user_profile; ?>" id="delay_button" class="button">Delay Upcoming Event</a>
			</div>
		<?php } ?>
			<button type="button" id="cal_legend">Legend</button>
			<table class="clmonth">
				<caption>
					<a href="<?php echo $_SERVER["PHP_SELF"] . "?user=" . $user_profile . "&month=". $prev_month . "&year=" . $prev_year; ?>" class="previous_link"></a>
					<span class="month"><?php echo $months[$cMonth-1].' '.$cYear; ?></span>
					<a href="<?php echo $_SERVER["PHP_SELF"] . "?user=" . $user_profile . "&month=". $next_month . "&year=" . $next_year; ?>" class="next_link"></a>
				</caption>
				<tr>
					<th>MON</th>
					<th>TUE</th>
					<th>WED</th>
					<th>THU</th>
					<th>FRI</th>
					<th>SAT</th>
					<th>SUN</th>
				</tr>
				<?php require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/printCal.php'); ?>
			</table>
		</div>
		<!-- Display noticeboard on staff member's profile -->
		<?php if(strpos($user_profile, "b00") === false) { ?>
			<div id="noticeboard">
				<h3 class="nb_header">Noticeboard</h3>
				<div id="posts_wrapper">
					<?php require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/printPosts.php'); ?>
				</div>
				<?php if($_SESSION['login_user'] === $user_profile) { ?>
					<form action="/project/controller/staff_add_post.php" method="post">
						<input type="hidden" name="user" value="<?php echo $user_profile ?>">
						<textarea name="post" cols="40" rows="3" placeholder="Create New Post..."></textarea>
						<input type="submit" value="Post">
					</form>
				<?php } ?>
			</div>

			<div id="post_popup">
				<a href="#" class="close-popup"><img src="/project/images/close-icon.png" /></a>
				<?php if($_SESSION['login_user'] === $user_profile) { ?>
					<button type="button" id="edit_post">Edit Post</button>
					<button type="button" id="delete_post">Delete Post</button>
				<?php } else { ?>
					<button type="button" id="view_post">View Post</button>
				<?php } ?>
			</div>
		<?php } ?>
	</div>

	<?php if(strpos($_SESSION['login_user'], "b00") === false && $_SESSION['login_user'] === $user_profile) { ?>
		<!-- Popup box that appears to create event -->
		<div id="popup">
			<h3 id="popup-header">Create New Event</h3>
			<a href="#" class="close-popup"><img src="/project/images/close-icon.png" /></a>

			<form action='/project/controller/staff_add_event.php' method='post'>
			<li class='form_row hidden'><label for='staff' class='form_title'>Staff Member:</label><input type='hidden' name='staff' value="<?php echo $_SESSION['login_user']; ?>"></li>
			<li class='form_row'><label for='title' class='form_title'>Event Title:</label><input type='text' name='title'></li>
			<li class='form_row'><label for='date' class='form_title'>Event Date:</label><input type='text' name='date' id='datepicker' min="<?php echo date('Y-m-d'); ?>" required></li>
			<li class='form_row'><label for='start-time' class='form_title'>Event Start Time:</label><input type='time' name='start-time' id='start-time' required></li>
			<li class='form_row'><label for='end-time' class='form_title'>Event End Time:</label><input type='time' name='end-time' id='end-time' step='1800' required></li>
			<li class='form_row'><label for='location' class='form_title'>Location: </label><input type='text' name='location'></li>
			<li class='form_row'><label for='description' class='form_title'>Description:</label><textarea name='description' cols='40' rows='6'></textarea></li>
			
			<input type='submit'></form>
		</div>

		<!-- Popup box that appears to edit post -->
		<div id="edit_popup">
			<h3 id="popup-header">Edit Post</h3>
			<a href="#" class="close-popup"><img src="/project/images/close-icon.png" /></a>
		</div>
	<?php } ?>

	<!-- Popup box that appears to view post -->
	<div id="legend_popup">
		<a href="#" class="close-popup"><img src="/project/images/close-icon.png" /></a>
		<div id="legend_wrapper">
			<div id="past" class='legend_des'><span class='color'></span><span class='info'>Past</div>
			<div id="weekend" class='legend_des'><span class='color'></span><span class='info'>Weekend</div>
			<div id="active" class='legend_des'><span class='color'></span><span class='info'>Available</div>
			<div id="no-events" class='legend_des'><span class='color'></span><span class='info'>Not Available</div>
		</div>
	</div>

	<!-- Popup box that appears to view post -->
	<div id="view_popup">
		<h3 id="popup-header">View Post</h3>
		<a href="#" class="close-popup"><img src="/project/images/close-icon.png" /></a>
	</div>
</body>
</html>