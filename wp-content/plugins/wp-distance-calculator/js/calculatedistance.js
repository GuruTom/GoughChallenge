// JavaScript Document
	jQuery( document ).ready(function() {
    
	jQuery("#calculate").click( function(){ 
        // This does the ajax request
		 var from = jQuery('#from').val();
	     var to = jQuery('#to').val();
		 
    jQuery.ajax({
	   
	    url : ajaxurl,
		type : 'POST',
        data: {
            'action':'distancewpcalculator',
			'from' : from,
			'to' : to            
        },
        success:function(data) {
            // This outputs the result of the ajax request
            jQuery('#result').html(data);
        },
        error: function(errorThrown){
            console.log(errorThrown);
        }
    });
    });
	
});
