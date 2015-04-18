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

	if(isset($_GET['student'])) {
		$student = $_GET['student'];
		$sql = "SELECT first_name, surname FROM student WHERE student_id = '$student'";
		$query = mysqli_query($connect, $sql);

		if(mysqli_num_rows($query) > 0) {
			$row = mysqli_fetch_assoc($query);
			$student = $row['first_name'] . " " . $row['surname'];
		}
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
			<h2 class="confirm_title">Event Confirmation</h2>
			<?php if(strpos($status, "Created") !== false) { ?>
				<!-- Add in created html -->
				<p>Event scheduled with <?php echo $staff; ?> on <?php echo $date; ?> from <?php echo $start ?> to <?php echo $end ?>.</p>
			<?php } else if(strpos($status, "Cancelled") !== false) { ?>
				<!-- Add in cancelled html -->
				<p>Event cancelled on <?php echo $date; ?> from <?php echo $start ?> to <?php echo $end ?>.</p>
			<?php } else if (strpos($status, "Delayed") !== false) { ?>
				<!-- Add in delayed html -->
				<p>Event delayed by <?php echo $delay_mins; ?> with <?php echo $student; ?> on <?php echo $date; ?> at <?php echo $start ?></p>
			<?php } ?>

			<a href="/project/views/profile.php?user='<?php echo $_SESSION['login_user']; ?>'" id="home-button" class="button">Home</a>
		</div>
	</div>
</body>
</html>