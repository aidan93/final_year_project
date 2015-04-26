$(document).ready(function() {

	//when availability button is clicked set new availability
	$("#availability a").click(function(event) {

		event.preventDefault();

		//Get availability indicator text
		var avail = $("#availability a span").text();

		//status contains the status of availability, 0 = busy, 1 = available
		var status;

		//Check status of current availability and change availability accordingly
		if(avail == 'Currently Available') {
			$("#availability a span").removeClass("available");
			$("#availability a span").addClass("busy");
			avail = "Currently Busy";
			status = "0";
		} else if(avail == 'Currently Busy') {
			$("#availability a span").removeClass("busy");
			$("#availability a span").addClass("available");
			avail = "Currently Available";
			status = "1";
		}

		//Set new availability indicator text
		$("#availability a span").text(avail);

		//Post user ID and availability status to staff_set_avail.php
		$.ajax({
			type: 'POST',
			url: '/project/controller/staff_set_avail.php',
			data: {
				user: getUrlParameter('user'),
				avail: status
			},
			error: function(xhr, status, error){
				console.log(error);
			}
		});
	});

	//function to get parameter from URL
	function getUrlParameter(sParam) {
	    var sPageURL = decodeURI(window.location.search.substring(1));
	    var sURLVariables = sPageURL.split('&');
	    for (var i = 0; i < sURLVariables.length; i++) {
	        var sParameterName = sURLVariables[i].split('=');
	        if (sParameterName[0] == sParam) {
	            return sParameterName[1];
	        }
	    }
	}
});