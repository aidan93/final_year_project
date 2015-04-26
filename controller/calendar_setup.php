<?php
	$months = Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
	
	//If month parameter is not set in the url then use current month
	if (!isset($_REQUEST["month"])) {
		$_REQUEST["month"] = date("n");
	} 
	
	//If year parameter is not set in the url then use current year
	if (!isset($_REQUEST["year"])) {
		$_REQUEST["year"] = date("Y");
	} 
	
	$cMonth = $_REQUEST["month"];
	$cYear = $_REQUEST["year"];
	$prev_year = $cYear;
	$next_year = $cYear;
	$prev_month = $cMonth-1;
	$next_month = $cMonth+1;

	//If previous month integer becomes 0, make it equal to month 12 of last year
	if ($prev_month == 0 ) {
		$prev_month = 12;
		$prev_year = $cYear - 1;
	}

	//If next month integer becomes 13, make it equal to month 1 of next year
	if ($next_month == 13 ) {
		$next_month = 1;
		$next_year = $cYear + 1;
	}
?>