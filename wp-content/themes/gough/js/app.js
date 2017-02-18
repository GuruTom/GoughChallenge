jQuery(document).ready( function($) {
	// When document has fully loaded

	$( function() {
		var locationForm = $( '#locationForm' );
		var results = $( 'div#result' );
		var formMessage = $( 'div.form-message' );
		var inputFields = $( 'input.required' );
		var submitButton = $( 'button.btn-submit' );

		$( locationForm ).submit( function( eve ) {
			eve.preventDefault();
			eve.stopPropagation();
			// Disable button
			submitButton.attr( 'disabled', 'disabled' );
			submitButton.addClass( 'disabled' );
			var currentLoc = $( '#currentLoc' ).val();
			var destLoc = $( '#destLoc' ).val();

			$.ajax({
				type: 'POST',
				url: locationAjax.ajaxurl,
				data: {
					'action': 'locationCalculate',
					'currentLocation': currentLoc,
					'destLocation': destLoc
				},
				success:function(data) {
				   $('#result').html(data);
			   },
			   error:function(errorThrown) {
				   submitButton.removeAttr('disabled');
					submitButton.removeClass('disabled');
					if ( errorThrown.responseText !== '' ) {
						$( formMessage ).text( errorThrown.responseText );
					} else {
						$( formMessage ).text( 'An error occured and your message could not be sent.' );
					}
					console.log( errorThrown );
			   }
		   })
	   });
	})
});
