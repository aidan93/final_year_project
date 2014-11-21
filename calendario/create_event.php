<?php

$username = "root";
$password = "root";
$hostname = "localhost"; 
$database = "project";
$month = mysql_real_escape_string($_POST['month']);
$day = mysql_real_escape_string($_POST['day']);
$year = mysql_real_escape_string($_POST['year']);

//connection to the database
$db = mysqli_connect($hostname, $username, $password, $database);

// Check connection
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}

$query = "INSERT INTO events (event_name, event_date, start_time, end_time) 
			VALUES ('Event', '$month-$day-$year', '9:00', '17:00')";

mysqli_query($db, $query);

mysqli_close($db);

printf($month."-".$day."-".$year);

?>