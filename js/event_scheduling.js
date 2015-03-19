$(document).ready(function() {

	//hide popup div on page load
	$("#popup").hide();

	//when timeslot is chosen, apply the class 'selected' to element
	$(".timeslot .choose_time, .timeslot .view_event").click(function(event){

		if($(this).parent().hasClass('selected')) {
			$(this).parent().removeClass('selected');
		} else {
			$(".timeslot").removeClass('selected');
			$(this).parent().addClass('selected');
		}
		
	});

	//redirects back to the calendar
	$("#cancel, #back").click(function(){
		parent.history.back();
		return false;
	});

	/* 
	** when user confirms timeslot, the date, start and end times are sent to get_selected_event.php 
	** which gets the relevant data from the database and displays it in the popup div in a form
	*/
	$("#confirm, .view_event").click(function(event){
		$.ajax({
		  type: 'POST',
		  url: '/project/controller/get_selected_event.php',
		  data: {
		  	user: $(".timeslot.selected").attr('data-user-profile'), 
		  	date: $(".timeslot.selected").attr('data-date'),
		  	selected_start: $(".timeslot.selected .start_time").text(),
		  	selected_end: $(".timeslot.selected .end_time").text()
		  },
		  success: function(data){
	        $("#popup").append(data);
	        $(".overlay").fadeIn();
			$("#popup").fadeIn();
	      },
	      error: function(xhr, status, error){
	        console.log(error);
	      }
		});
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

	//function to close the current popup
	function closePopup() {
		$(".overlay").fadeOut();
		$.when($("#popup").fadeOut()).done(function() {
		    $("#popup form").remove();
		});
	    $(".timeslot").removeClass('selected');
	}
});