$(document).ready(function() {

	//when timeslot is chosen, apply the class 'selected' to element
	$(".timeslot .delay_event").click(function(event){

		if($(this).parent().hasClass('selected')) {
			$(this).parent().removeClass('selected');
		} else {
			$(".timeslot").removeClass('selected');
			$(this).parent().addClass('selected');
		}
		
	});

	//redirects back to the calendar
	$("#back").click(function(){
		parent.history.back();
		return false;
	});


	//when the delay event button is clicked a popup box shows minutes ranging from 5-20
	$(".delay_event").click(function(event){
		if($(".timeslot.selected .delay_times").val() !== '0') {
			if (confirm('Are you sure you want to delay this event by ' + $(".timeslot.selected .delay_times").val() + ' minutes?')) {
	           $.ajax({
				  type: 'POST',
				  url: '/project/controller/delay_event.php',
				  data: {
				  	user: $(".timeslot.selected").attr('data-user-profile'), 
				  	date: $(".timeslot.selected").attr('data-date'),
				  	selected_start: $(".timeslot.selected .start_time").text(),
				  	selected_end: $(".timeslot.selected .end_time").text(),
				  	delay: $(".timeslot.selected .delay_times").val()
				  },
				  success: function(data){
			        document.location.href = data;
			      },
			      error: function(xhr, status, error){
			        console.log(error);
			      }
				});
		    } else {
	           return false;
		    }
		}
	});
});