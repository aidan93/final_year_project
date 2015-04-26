<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/connect.php');

	//set up calendar according to current date
	$timestamp = mktime(0,0,0,$cMonth,1,$cYear);
	$totalday = date("t",$timestamp);
	$thismonth = getdate ($timestamp);
	$firstday = $thismonth['wday'];
	$firstday = ($firstday + 6) % 7;

	$today = date('Y-m-d');

	//Go through each day of the month and determine if it is past, unavailable, weekend or avaiable day
	for ($i=0; $i<($totalday+$firstday); $i++) {

		if(($i - $firstday + 1) > 0) {
			$day = ($i - $firstday + 1);
			$date = $cYear . '-' . sprintf('%02s', $cMonth) . '-' . sprintf('%02s', $day);

			// Check if it is a student or staff member profile and get their relevant events
			if(strpos($user_profile, "b00") !== false) {
				$sql = "SELECT * FROM events WHERE event_date = '$date' AND student_id = '$user_profile'";
			} else {
				$sql = "SELECT * FROM events WHERE event_date = '$date' AND staff_id = '$user_profile'";
			}

			$query = mysqli_query($connect, $sql);
			
			// Run a quick check to verify there are any results
			$quick_check = mysqli_num_rows($query);

			//if date is a monday, begin new row
			if(($i % 7) == 0 ) {
				echo "<tr>";
			}

			//Get the integer for the day of the week and add the string associated with that day to the date
			$weekday = $i % 7;
			switch($weekday) {
				case "0":
					$weekday = "Monday";
					break;
				case "1":
					$weekday = "Tuesday";
					break;
				case "2":
					$weekday = "Wednesday";
					break;
				case "3":
					$weekday = "Thursday";
					break;
				case "4":
					$weekday = "Friday";
					break;
				case "5":
					$weekday = "Saturday";
					break;
				case "6":
					$weekday = "Sunday";
					break;
			}

			/* 
			** if weekend then apply styling to indicate this
			** else check if there are events available for this date
			** and that it is also later than current date
			*/
			if(($i % 7 ==6) || ($i % 7 ==5)) {

				//check if day has passed or not
				if($date >= $today) {
					echo "<td class='weekend'><span class='date'>". $day ."</span><span class='weekday'>".$weekday."</span></td>";
				} else {
					echo "<td class='past'><span class='date'>". $day ."</span><span class='weekday'>".$weekday."</span></td>";
				}				

			} else {

				//if there are events available for the date and the date is today or in the future then process the events
				if($quick_check !== 0 && $date >= $today) {
					$slots = 0;

					while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
						$status = $row['status'];

						/* if event status equals 0 and logged in user is a student and not viewing their profile, add 1 to $slots
						** or if event status equals 0 and logged in user is a staff member and viewing their profile, add 1 to $slots
						** or if event status equals 1 and logged in user is viewing their profile, add 1 to $slots
						*/
						if(($status == 0 && $_SESSION['login_user'] !== $user_profile && strpos($_SESSION['login_user'], "b00") !== false) || 
							($status == 0 && $_SESSION['login_user'] === $user_profile && strpos($_SESSION['login_user'], "b00") === false) || 
							($status == 1 && $_SESSION['login_user'] === $user_profile)) {
							$slots++;
						}
					}

					//If $slots is greater than 0 then make day available in user's calendar
					if($slots > 0) {
					
						echo "<td class='available'><a href=/project/views/schedule_event.php?user=". $user_profile ."&date=". $date ."><span class='date'>". $day ."</span><span class='weekday'>".$weekday."</span></a></td>";
					} else {
						echo "<td class='unavailable'><span class='date'>". $day ."</span><span class='weekday'>".$weekday."</span></td>";
					}
					
				} else {

					/*
					** If date is greater or equal to today then set day as unavailable since no events are set up
					** however, if the date is less than today then set the day as a past day
					*/
					if($date >= $today) {
						echo "<td class='unavailable'><span class='date'>". $day ."</span><span class='weekday'>".$weekday."</span></td>";
					} else {
						echo "<td class='past'><span class='date'>". $day ."</span><span class='weekday'>".$weekday."</span></td>";
					}
				}
			}
			//If the end of the week, end row
			if(($i % 7) == 6 ) { 
				echo "</tr>";
			}
			//If month ends before week end then fill the rest of the row with empty cells
			if($i == ($totalday + $firstday)- 1) {
				while((($i+1) % 7) != 0) {
					$i++;
					echo "<td class='next'></td>";
				}
			}
		} else { //If month does not start at the beginning of the week, fill the beginning of the week with empty cells
			echo "<td class='previous'></td>";
		}	
	}

	echo "</tr>";
	
?>