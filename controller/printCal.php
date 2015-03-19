<?php
	include($_SERVER['DOCUMENT_ROOT'].'/project/controller/connect.php');

	//set up calendar according to current date
	$timestamp = mktime(0,0,0,$cMonth,1,$cYear);
	$totalday = date("t",$timestamp);
	$thismonth = getdate ($timestamp);
	$firstday = $thismonth['wday'];
	$firstday = ($firstday + 6) % 7;

	$today = date('Y-m-d');

	//Go through each day of the month and add event (if available)
	for ($i=0; $i<($totalday+$firstday); $i++) {

		if(($i - $firstday + 1) > 0) {
			$day = ($i - $firstday + 1);
			$date = $cYear . '-' . sprintf('%02s', $cMonth) . '-' . sprintf('%02s', $day);

			// Check if it is a student of staff member logged in and get their relevant events
			if(strpos($user_profile, "b00") !== false) {
				$sql = "SELECT * FROM events WHERE event_date = '$date' AND student_id = '$user_profile'";
			} else {
				$sql = "SELECT * FROM events WHERE event_date = '$date' AND staff_id = '$user_profile'";
			}

			$query = mysqli_query($connect, $sql) or die (mysqli_error($connect));
			
			// Run a quick check to verify there are any results
			$quick_check = mysqli_num_rows($query);

			if(($i % 7) == 0 ) {
				echo "<tr>";
			}

			/* 
			** if weekend then apply styling to indicate this
			** else check if there are events available for this date
			** and that it is also later than current date
			*/
			if(($i % 7 ==6) || ($i % 7 ==5)) {

				echo "<td class='weekend' data-date=".$date.">". $day ."</td>";
			} else {

				if($quick_check != 0 && $date >= $today) {
					$slots = 0;

					while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
						$status = $row['status'];
						//$student = $row['student_id'];

						/* if current profile is not logged in user then check for status 0 and show free events
						**
						** or if current profile is logged in user then check for status 1 and show user's events
						*/
						if(($status == 0 && $_SESSION['login_user'] !== $user_profile) || ($status == 1 && $_SESSION['login_user'] == $user_profile)) {
							$slots++;
						}
					}

					if($slots > 0) {
					
						echo "<td class='available'><a href=/project/views/schedule_event.php?user=". $user_profile ."&date=". $date .">". $day ."</a></td>";
					} else {
						echo "<td class='unavailable'>". $day ."</td>";
					}
					
				} else {
					
					echo "<td class='unavailable'>". $day ."</td>";
				}
			}
			if(($i % 7) == 6 ) { 
				echo "</tr>";
			}
			if($i == ($totalday + $firstday)- 1) {
				while((($i+1) % 7) != 0) {
					$i++;
					echo "<td class='next'></td>";
				}
			}
		} else {
			echo "<td class='previous'></td>";
		}	
	}

	echo "</tr>";

	mysqli_close($connect);
?>