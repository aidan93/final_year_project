<?php

//allow access to regular users
$access = 'allow';

require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/session.php');

$user_profile = $_GET['user'];
?>
<!DOCTYPE html>
<html>
<head>
	<title>Schedule Event</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,700,400' rel='stylesheet' type='text/css'>
	<link href="/project/style/style.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	<script type="text/javascript" src="/project/js/event_scheduling.js"></script>
	<script type="text/javascript" src="/project/js/event_delay.js"></script>
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
		<div id="events">
			<div id="event_header">
				<span id="header_starttime">START TIME</span>
				<span id="header_endtime">END TIME</span>
				<span id="header_time">TIME</span>
			</div>
			<div id="event_times"><?php require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/events.php'); ?></div>
			<?php if(strpos($_SESSION['login_user'], "b00") !== false && $_SESSION['login_user'] !== $user_profile) { ?>
				<div id="buttons">
					<button type="button" id="cancel">Cancel</button>
					<button type="button" id="confirm">Confirm</button>
				</div>
			<?php } else if($_SESSION['login_user'] === $user_profile){ ?>
				<div id="buttons">
					<button type="button" id="back">Back</button>
				</div>
			<?php } ?>
		</div>
	</div>

	<!-- Popup box that appears when timeslot has been selected -->
	<div id="popup">
		<h3 id="popup-header">Event Details</h3>
		<a href="#" class="close-popup"><img src="/project/images/close-icon.png" /></a>
	</div>
</body>
</html>