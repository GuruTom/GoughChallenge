$(document).ready(function() {
	// When document has fully loaded

	// When Button has clicked store data into vars
	jQuery(".btn-submit").click( function(){
		// This does the ajax request
		 var from = jQuery('#currentLoc').val(); //takes users location
		 var to = jQuery('#destLoc').val(); // Takes destination location

		 $.ajax({
			 type: 'POST',
			 url: locationAjax.ajaxurl,
			 data: {
				 'action': 'locationCalculate',
				 'currentLocation': currentLoc,
				 'destLocation': destLoc
			 },
			 success:function(data) {
			 	jQuery('#result').html(data);
			},
			error:function(errorThrown) {
				console.log(errorThrown);
			}
		});
	});
});
