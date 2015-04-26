$(document).ready(function() {

	//hide popup div on page load
	$("#popup, #staff_popup, #student_popup, #search-popup").hide();

	//open staff popup window if create staff is clicked
	$("#add_staff").click(function(event) {
		$(".overlay").fadeIn();
		$("#staff_popup").fadeIn();
	});

	//open student popup window if create student is clicked
	$("#add_student").click(function(event) {
		$(".overlay").fadeIn();
		$("#student_popup").fadeIn();
	});

	//open search popup window if edit button clicked
	$(".edit").click(function(event) {
		$(".overlay").fadeIn();
		$("#search-popup").fadeIn();
	});

	//when search button is clicked or enter pressed, get data from search text and pass to search_user.php
	$("#search_button").click(function(event) {
		searchUser();
	});
	$('#search_text').keypress(function (e) {
		if (e.which == 13) {
			searchUser();
		}
	});

	/* when user is clicked, load their data
	** element is dynamically created so click function is different to those above
	*/
	$('body').on('click', '.search_result a.result', function() {
	    if($(this).parent().hasClass('selected')) {
			$(this).parent().removeClass('selected');
		} else {
			$(".search_result.selected").removeClass('selected');
			$(this).parent().addClass('selected');
		}

		$.ajax({
			type: 'POST',
			url: '/project/controller/admin_get_user.php',
			data: {
				user: $(".search_result.selected .user_id").text(),
			},
			success: function(data){
				//clear any data from last search
				clearPopup();
				$("#popup").append(data);
				$("#popup").fadeIn();
			},
			error: function(xhr, status, error){
				console.log(error);
			}
		});
	});

	//when delete button is clicked, delete that user from the database
	$('body').on('click', '#popup form #delete_user', function() {
		if (confirm('Are you sure you want to delete this user?')) {
			$.ajax({
				type: 'POST',
				url: '/project/controller/admin_delete_user.php',
				data: {
					user: $("#popup form #user_id").val(),
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

	//get status parameter from url and on confirm remove it
	if(getUrlParameter('status')) {
		var status = getUrlParameter('status');
		if(confirm('User successfully ' + status)) {
			location.href=location.href.replace(/&?status=([^&]$|[^&]*)/i, "");
		}
	}

	//get error parameter from url and on confirm remove it
	if(getUrlParameter('error')) {
		var error = getUrlParameter('error');

		if(error == 'exists') {
			if(confirm('User already exists.')) {
				location.href=location.href.replace(/&?error=([^&]$|[^&]*)/i, "");
			}
		} else if(error == 'insert') {
			if(confirm('Error adding record to database.')) {
				location.href=location.href.replace(/&?error=([^&]$|[^&]*)/i, "");
			}
		} else if(error == 'delete') {
			if(confirm('Error deleting record from database')) {
				location.href=location.href.replace(/&?error=([^&]$|[^&]*)/i, "");
			}
		} else if(error == 'password') {
			if(confirm('Invalid password. Password must contain at least: 1 uppercase letter, 1 lowercase letter, 1 number and at least 8 characters long.')) {
				location.href=location.href.replace(/&?error=([^&]$|[^&]*)/i, "");
			}
		}
	}

	//function to close the current popup
	function closePopup() {
		$(".overlay").fadeOut();
		$("#popup, #staff_popup, #student_popup, #search-popup").fadeOut();
	}

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

	function searchUser() {
		$.ajax({
			type: 'POST',
			url: '/project/controller/search_user.php',
			data: {
				user: $("#search_text").val(),
			},
			success: function(data){
				$("#search-popup").fadeOut();
				//clear any data from last search
				clearPopup();
				$("#popup").append(data);
				$("#popup").fadeIn();
			},
			error: function(xhr, status, error){
				console.log(error);
			}
		});
	}

	function clearPopup() {
		$("#popup h3, #popup #search_header, #popup .search_result, #popup form").remove();
	}
});