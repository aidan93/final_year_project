<?php 

include($_SERVER['DOCUMENT_ROOT'].'/project/controller/session.php');

?>

<html>
<head>
	<title>Delay Today's Events</title>
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,700,400' rel='stylesheet' type='text/css'>
	<link href="/project/style/style.css" rel="stylesheet" type="text/css">
	<!-- <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/themes/smoothness/jquery-ui.css" /> -->

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<!-- <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js"></script> -->
	<script type="text/javascript" src="/project/js/event_delay.js"></script>
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
		<?php include($_SERVER['DOCUMENT_ROOT'].'/project/controller/todays_events.php'); ?>
	</div>
</body>
</html>