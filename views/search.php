<?php 

//allow access to regular users
$access = 'allow';

require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/session.php');

?>
<!DOCTYPE html>
<html>
<head>
	<title>Search Results</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,700,400' rel='stylesheet' type='text/css'>
	<link href="/project/style/style.css" rel="stylesheet" type="text/css">
<body>
	<div id="header">
		<div id="profile">
			<span id="welcome"><?php echo $login_session; ?></span>
			<b id="logout"><a href="/project/views/logout.php">Log Out</a></b>
		</div>
		<a href="/project/index.php" id="logo"><img src="/project/images/logo.png" /></a>
	</div>

	<div class="wrapper">
			<?php

			//Get search information
			$search = mysqli_real_escape_string($connect, $_POST['search_name']);

			//Check database for staff member with similar name to that of search input
			$sql = "SELECT staff_id, first_name, surname, room_number, email FROM staff WHERE CONCAT(first_name, ' ', surname) LIKE '%$search%' OR surname LIKE '%$search%'";

			$query = mysqli_query($connect, $sql);
			
			//if there are results from the search then print out the relevant information on staff
			if(mysqli_num_rows($query) > 0) {

				echo "<h3>" . mysqli_num_rows($query) . " " . "result(s) found</h3>";
			?>
				<div id="search_results">
			<?php	
				while($result = mysqli_fetch_assoc($query)) {
					$user_id = $result['staff_id'];
					$first_name = $result['first_name'];
					$surname = $result['surname'];
					$room = $result['room_number'];

					//Dont display admin user record
					if(strpos($user_id, 'e00000') === false) {
						echo "<div class='search_result'><a href=http://" . $_SERVER['HTTP_HOST'] . "/project/views/profile.php?user=" . $user_id . "><span class='staff_name'>" . $first_name . " " . $surname . "</span><span class='room'>Room: " . $room . "</span></a></div>";
					}	
				}
			} else {
				echo("No staff member results for '" . $search . "'");
			}

			?>
		</div>
	</div>

</body>
</html>