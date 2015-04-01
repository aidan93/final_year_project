<?php
include($_SERVER['DOCUMENT_ROOT'].'/project/controller/session.php');
include($_SERVER['DOCUMENT_ROOT'].'/project/controller/calendar_setup.php');
include($_SERVER['DOCUMENT_ROOT'].'/project/controller/google_cal_connect.php');

//Get the user ID of profile
if(isset($_GET["user"])) {
	$user_profile = $_GET["user"];
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Your Home Page</title>
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,700,400' rel='stylesheet' type='text/css'>
	<link href="/project/style/style.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/themes/smoothness/jquery-ui.css" />

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js"></script>
	<!-- <script type="text/javascript" src="/project/js/search.js"></script> -->
	<script type="text/javascript" src="/project/js/create_event.js"></script>
</head>
<body>
	<div class="overlay"></div>
	<div id="header">
		<a href="/project/index.php" id="logo"><img src="/project/images/logo.png" /></a>
		<div id="profile">
			<span id="welcome"><?php echo $login_session; ?></span>
			<b id="logout"><a href="/project/views/logout.php">Log Out</a></b>
		</div>
		<form method="post" action="search.php" id="searchform"> 
		    <input type="text" name="search_name" placeholder="Search Staff"> 
		    <button type="submit"></button> 
		</form> 
	</div>
	<div class="wrapper">
		<!-- Controls to add events -->
		<?php if(strpos($_SESSION['login_user'], "b00") === false && $_SESSION['login_user'] === $user_profile) { ?>
			<div id="staff_controls">
				<button type="button" id="staff_add_event">Create New Event</button>
				<a href="/project/views/delay_view.php?user=<?php echo $user_profile; ?>" id="delay_button" class="button">Delay Event</a>
			</div>
		<?php } ?>
	<table class="clmonth">
		<caption>
			<a href="<?php echo $_SERVER["PHP_SELF"] . "?user=" . $user_profile . "&month=". $prev_month . "&year=" . $prev_year; ?>" class="previous_link"></a>
			<?php echo $months[$cMonth-1].' '.$cYear; ?>
			<a href="<?php echo $_SERVER["PHP_SELF"] . "?user=" . $user_profile . "&month=". $next_month . "&year=" . $next_year; ?>" class="next_link"></a>
		</caption>
		<tr>
			<th>MONDAY</th>
			<th>TUESDAY</th>
			<th>WEDNESDAY</th>
			<th>THURSDAY</th>
			<th>FRIDAY</th>
			<th>SATURDAY</th>
			<th>SUNDAY</th>
		</tr>
		<?php include($_SERVER['DOCUMENT_ROOT'].'/project/controller/printCal.php'); ?>
	</table>
	</div>

	<?php if(strpos($_SESSION['login_user'], "b00") === false && $_SESSION['login_user'] === $user_profile) { ?>
		<!-- Popup box that appears to create event -->
		<div id="popup">
			<h3 id="popup-header">Create New Event</h3>
			<a href="#" class="close-popup"><img src="/project/images/close-icon.png" /></a>

			<form action='/project/controller/staff_add_event.php' method='post'>
			<li class='form_row hidden'><label for='staff' class='form_title'>Staff Member:</label><input type='hidden' name='staff' value="<?php echo $_SESSION['login_user'] ?>"></li>
			<li class='form_row'><label for='title' class='form_title'>Event Title:</label><input type='text' name='title'></li>
			<li class='form_row'><label for='date' class='form_title'>Event Date:</label><input type='text' name='date' id='datepicker' required></li>
			<li class='form_row'><label for='start-time' class='form_title'>Event Start Time:</label><input type='time' name='start-time' id='start-time' required></li>
			<li class='form_row'><label for='end-time' class='form_title'>Event End Time:</label><input type='time' name='end-time' id='end-time' required></li>
			<li class='form_row'><label for='location' class='form_title'>Location: </label><input type='text' name='location'></li>
			<li class='form_row'><label for='description' class='form_title'>Description:</label><textarea name='description' cols='40' rows='6'></textarea></li>
			
			<input type='submit'></form>
		</div>
	<?php } ?>
</body>
</html>