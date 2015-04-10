$(document).ready(function() {

	//hide popup div on page load
	$("#popup").hide();

	$("#staff_add_event").click(function(event) {

		$(".overlay").fadeIn();
		$("#popup").fadeIn();
		
	});

	//when start time is selected, increased by 30 mins and add value to end time minimum value
	$("#start-time").change(function() {

		//get start time
		var start = $(this).val();

		//add 30 mins to start time
		var d = new Date();
		var theDate = d.getFullYear() + '-' + ( d.getMonth() + 1 ) + '-' + d.getDate();
		var theTime = theDate + " " + start;
		var endTime = new Date(Date.parse(theTime) + 30*60*1000);

		//get the hours and mins of end time
		var hours = endTime.getHours();
		var mins = endTime.getMinutes();

		//if the hours or mins of end time is less than 10 then concantenate 0 to beginning
		if(hours < 10) {
			hours = "0" + hours;
		}

		if(mins < 10) {
			mins = "0" + mins;
		}

		//set value of end time input to 30 mins after start time
		$("#end-time").attr("min", hours + ":" + mins + ":00");
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