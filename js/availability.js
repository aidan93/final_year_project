$(document).ready(function() {

	//when availability button is clicked set new availability
	$("#availability a").click(function(event) {

		event.preventDefault();

		var avail = $("#availability a span").text();

		//status contains the status of availability, 0 = busy, 1 = available
		var status;

		if(avail == 'Available') {
			$("#availability a span").removeClass("available");
			$("#availability a span").addClass("busy");
			avail = "Busy";
			status = "0";
		} else if(avail == 'Busy') {
			$("#availability a span").removeClass("busy");
			$("#availability a span").addClass("available");
			avail = "Available";
			status = "1";
		}

		$("#availability a span").text(avail);

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