$(document).ready(function() {
	//hide popup div on page load
	$("#legend_popup").hide();


	//open legend popup when legend button clicked
	$("#cal_legend").click(function(event) {
		event.preventDefault();

		var pos = $(this).offset();
    	var h = $(this).height();
    	var w = $(this).width();

    	$("#legend_popup").css({ left: pos.left + w, top: pos.top + h + 15});
		$("#legend_popup").fadeIn();
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
		$("#legend_popup").fadeOut();
	}
});