$(document).ready(function() {
	//hide popup div on page load
	$("#post_popup, #edit_popup").hide();

	//open post popup when dropdown icon clicked
	$(".post .post-icon").click(function(event) {
		event.preventDefault();

		var post_id = $(this).parent().attr('data-post-id');
		var pos = $(this).offset();
    	var h = $(this).height();
    	var w = $(this).width();

    	$("#post_popup").css({ left: pos.left - w - 80, top: pos.top + h});
    	$("#post_popup").attr('data-post-id', post_id);
		$("#post_popup").fadeIn();
	});

	//when edit button is clicked get post data and add to popup
	$("#edit_post").click(function(event) {
		$.ajax({
			type: 'POST',
			url: '/project/controller/staff_get_post.php',
			data: {
				post: $("#post_popup").attr("data-post-id"),
			},
			success: function(data){
				$("#post_popup").fadeOut();
				$("#edit_popup form").remove();
				$("#edit_popup").append(data);
				$(".overlay").fadeIn();
				$("#edit_popup").fadeIn();
			},
			error: function(xhr, status, error){
				console.log(error);
			}
		});
	});

	//when delete button is clicked, remove post from database
	$("#delete_post").click(function(event) {
		if (confirm('Are you sure you want to delete this post?')) {
			$.ajax({
				type: 'POST',
				url: '/project/controller/staff_delete_post.php',
				data: {
					post: $("#post_popup").attr("data-post-id"),
					user: getUrlParameter('user')
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

	//get error parameter from url and on confirm remove it
	if(getUrlParameter('error')) {
		var error = getUrlParameter('error');

		if(error == 'insert') {
			if(confirm('Error adding record to database.')) {
				location.href=location.href.replace(/&?error=([^&]$|[^&]*)/i, "");
			}
		} else if(error == 'delete') {
			if(confirm('Error deleting record from database')) {
				location.href=location.href.replace(/&?error=([^&]$|[^&]*)/i, "");
			}
		} else if(error == 'edit') {
			if(confirm('Error editing record in database')) {
				location.href=location.href.replace(/&?error=([^&]$|[^&]*)/i, "");
			}
		}
	}

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
		$("#post_popup, #edit_popup").fadeOut();
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

});