<?php

//allow access to regular users
$access = 'allow';

require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/session.php');

//Get relevant details from url to display correct information on confirmation page
if(isset($_GET['status'])) {

	//Status is used to check what event has taken place
	$status = $_GET['status'];
	
	if(isset($_GET['staff'])) {
		$staff = $_GET['staff'];
	}

	if(isset($_GET['date'])) {
		$date = date('r', strtotime($_GET['date']));
		$date = date('d/m/Y', strtotime($date));
	}

	if(isset($_GET['start'])) {
		$start = date('H:i', strtotime($_GET['start']));
	}

	if(isset($_GET['end'])) {
		$end = date('H:i', strtotime($_GET['end']));
	}

	if(isset($_GET['delay'])) {
		$delay_mins = $_GET['delay'] . " minutes";
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Event Confirmation</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,700,400' rel='stylesheet' type='text/css'>
	<link href="/project/style/style.css" rel="stylesheet" type="text/css">
</head>
<body>
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
		<div id="confirmation">
			<?php if(strpos($status, "Free") !== false) { ?>
				<!-- Confirm free time events have been created -->
				<h2 class="confirm_title">Free Time Events Created</h2>
				<p>Free time events created from <?php echo $start ?> to <?php echo $end ?> on <?php echo $date; ?>.</p>
			<?php } else if(strpos($status, "Created") !== false) { ?>
				<!-- Confirm event has been scheduled with staff member -->
				<h2 class="confirm_title">Event Scheduled</h2>
				<p>Event scheduled with <?php echo $staff; ?> on <?php echo $date; ?> from <?php echo $start ?> to <?php echo $end ?>.</p>
			<?php } else if(strpos($status, "Cancelled") !== false) { ?>
				<!-- Confirm event has been cancelled -->
				<h2 class="confirm_title">Event Cancelled</h2>
				<p>Event cancelled on <?php echo $date; ?> from <?php echo $start ?> to <?php echo $end ?>.</p>
			<?php } else if (strpos($status, "Delayed") !== false) { ?>
				<!-- Confirm event has been delayed -->
				<h2 class="confirm_title">Event Delayed</h2>
				<p>Event delayed by <?php echo $delay_mins; ?> on <?php echo $date; ?> at <?php echo $start ?></p>
			<?php } else if (strpos($status, "Exceeded") !== false) { ?>
				<!-- Notify staff member that event has exceeded the delay limit -->
				<h2 class="confirm_title">Delay Limit Exceeded</h2>
				<p>Event scheduled for <?php echo $date; ?> at <?php echo $start ?> has exceeded the maximum 20 minute delay. Please allow at least 10 minutes for each event.</p>
			<?php } else if (strpos($status, "Edited") !== false) { ?>
				<!-- Confirm event has been edited -->
				<h2 class="confirm_title">Event Edited</h2>
				<p>Event edited on <?php echo $date; ?> at <?php echo $start ?></p>
			<?php } else if (strpos($status, "Error") !== false) { ?>
				<!-- Inform of event conflict -->
				<h2 class="confirm_title">Event Conflict</h2>
				<p>Event conflict on <?php echo $date; ?> between the times <?php echo $start ?> and <?php echo $end ?></p>
			<?php } else if(strpos($status, "Google") !== false) { ?>
				<!-- Inform of Google error -->
				<h2 class="confirm_title">Google Calendar Error</h2>
				<p>Error with Google calendar occurred. Please try again.</p>
			<?php } ?>

			<a href="/project/views/profile.php?user='<?php echo $_SESSION['login_user']; ?>'" id="home-button" class="button">Home</a>
		</div>
	</div>
</body>
</html>