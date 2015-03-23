<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include($_SERVER['DOCUMENT_ROOT'].'/project/controller/session.php');

if(isset($_GET['status'])) {
	$status = $_GET['status'];
	
	if(isset($_GET['staff'])) {
		$staff = $_GET['staff'];
	}

	if(isset($_GET['student'])) {
		$student = $_GET['student'];
	}

	if(isset($_GET['mins'])) {
		$delay_mins = $_GET['mins'];
	}
}

?>
<html>
<head>
	<title>Event Confirmation</title>
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,700,400' rel='stylesheet' type='text/css'>
	<link href="/project/style/style.css" rel="stylesheet" type="text/css">
</head>
<body>
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
		<div id="confirmation">
			<h2 class="confirm_title">Event Confirmation</h2>
			<?php if(strpos($status, "Created") !== false) { ?>
				<!-- Add in created html -->
				<p>Event scheduled with <?php echo $staff; ?></p>
			<?php } else if(strpos($status, "Cancelled") !== false) { ?>
				<!-- Add in cancelled html -->
				<p>Event Cancelled.</p>
			<?php } else if (strpos($status, "Delayed") !== false) { ?>
				<!-- Add in delayed html -->
				<p>Event delayed by <?php echo $delay_mins; ?> with <?php echo $student; ?></p>
			<?php } ?>
		</div>
	</div>
</body>
</html>