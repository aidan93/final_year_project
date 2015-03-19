<?php 

$username = "root";
$password = "root";
$hostname = "localhost"; 
$database = "project";

//connection to the database
$connect = mysqli_connect($hostname, $username, $password, $database);

// Check connection
if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

?>