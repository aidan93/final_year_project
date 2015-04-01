$(document).ready(function() {

	//hide popup div on page load
	$("#popup").hide();

	$("#staff_add_event").click(function(event) {

		$(".overlay").fadeIn();
		$("#popup").fadeIn();
		
	});

	//close popup when X is clicked
	$(".close-popup").click(function(event){
		event.preventDefault();
		closePopup();
	});

	//close popup wehn ESC is pressed
	$(document).keyup(function(event) {
	    if(event.which === 27) {
	    	closePopup();
	    }
	});

	//add datepicker for date field
	$("#datepicker").datepicker({
		minDate: 0,
		beforeShowDay: noWeekend,
		dateFormat: 'yy-mm-dd'
	});

	//prevent the ability to choose weekends for events
	function noWeekend(date) {
		var day = date.getDay();
		return [(day > 0 && day < 6), ''];
	}

	//function to close the current popup
	function closePopup() {
		$(".overlay").fadeOut();
		$("#popup").fadeOut();
	}
});