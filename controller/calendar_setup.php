<?php
	$months = Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
	
	if (!isset($_REQUEST["month"])) {
		$_REQUEST["month"] = date("n");
	} 
	
	if (!isset($_REQUEST["year"])) {
		$_REQUEST["year"] = date("Y");
	} 
	
	$cMonth = $_REQUEST["month"];
	$cYear = $_REQUEST["year"];
	$prev_year = $cYear;
	$next_year = $cYear;
	$prev_month = $cMonth-1;
	$next_month = $cMonth+1;

	if ($prev_month == 0 ) {
		$prev_month = 12;
		$prev_year = $cYear - 1;
	}
	if ($next_month == 13 ) {
		$next_month = 1;
		$next_year = $cYear + 1;
	}
?>