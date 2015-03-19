<?php 

include($_SERVER['DOCUMENT_ROOT'].'/project/controller/session.php');

?>

<html>
<head>
	<title>Search Results</title>
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,700,400' rel='stylesheet' type='text/css'>
	<link href="/project/style/style.css" rel="stylesheet" type="text/css">
	<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
	<script type="text/javascript" src="/project/js/search.js"></script> -->
</head>
<body>
	<div id="header">
		<a href="/project/index.php" id="logo"><img src="/project/images/logo.png" /></a>
		<div id="profile">
			<span id="welcome"><?php echo $login_session; ?></span>
			<b id="logout"><a href="/project/views/logout.php">Log Out</a></b>
		</div>
	</div>

	<div class="wrapper">
		<div id="search_results">
			<?php

			//Get search information
			$search = mysqli_real_escape_string($connect, $_POST['search_name']);

			//Check database for staff member with similar name to that of search input
			$sql = "SELECT staff_id, first_name, surname, room_number, email FROM staff WHERE CONCAT(first_name, ' ', surname) LIKE '%$search%' OR surname LIKE '%$search%'";

			$query = mysqli_query($connect, $sql);

			//if there are results from the search then print out the relevant information on staff
			if(mysqli_num_rows($query) > 0) {
				while($result = mysqli_fetch_assoc($query)) {
					$user_id = $result['staff_id'];
					$first_name = $result['first_name'];
					$surname = $result['surname'];

					echo mysqli_num_rows($query) . " " . "result(s) found <br>";

					echo "<a href=http://" . $_SERVER['HTTP_HOST'] . "/project/views/profile.php?user=" . $user_id . ">" . $first_name . " " . $surname . "</a>";
				}
			} else {
				echo("No Results for '" . $search . "'");
			}

			?>
		</div>
	</div>

</body>
</html>