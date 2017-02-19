jQuery(document).ready( function($) {
	// When document has fully loaded

	$( function() {

		//Declare Varibles
		var locationForm = $( '#locationForm' );
		var message = $( 'div.form-message' );
		var button = $( 'button.btn-submit' );

		$( locationForm ).submit( function( eve ) {
			//Prevent defualt behaviour
			eve.preventDefault();
			eve.stopPropagation();

			// Get uesers input
			var currentLoc = $( '#currentLoc' ).val();
			var destLoc = $( '#destLoc' ).val();

			//Pass inputs through to functions
			$.ajax({
				type: 'POST',
				url: locationAjax.ajaxurl,
				data: {
					'action': 'locationCalculate',
					'currentLocation': currentLoc,
					'destLocation': destLoc
				},
				success:function(data) {
					//return the distance
				   $('#result').html(data);
			   },
			   error:function(errorThrown) {
				   // Return an error 

					if ( errorThrown.responseText !== '' ) {
						$( message ).text( errorThrown.responseText );
					} else {
						$( message ).text( 'An error occured and your message could not be sent.' );
					}
					console.log( errorThrown );
			   }
		   })
	   });
	})
});
