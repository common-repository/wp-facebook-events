function CreateFBEvent(meta) {
    jQuery.post(
            FacebookEvents.ajaxurl,
            {
                action: 'fbevents_createevent',
                eventname: jQuery('#eventname').val(),
                eventstart: jQuery('#eventstart').val(),
                eventend: jQuery('#eventend').val(),
                eventlocation: jQuery('#eventlocation').val(),
                eventstreet: jQuery('#eventstreet').val(),
                eventcity: jQuery('#eventcity').val(),
                eventdescription: jQuery('#eventdescription').val(),
                fbEventsNonce: FacebookEvents.fbEventsNonce,
                meta: meta
            },
            function ( response ) {
                // do noting
                if (response.match(/^Unable/)) {
                    jQuery('#ajaxresult').text(response);
                } else {
                    if (!meta) {
                        jQuery('#ajaxresult').text("Event created successfully.");
                        jQuery('#eventlistbody').html(response);
                    } else {
                        jQuery('#ajaxresult').text(response);
                    }
                }
                
            } 
    );
}
jQuery(document).ready( function() {
            jQuery( '#eventstart' ).datetimepicker();
            jQuery( '#eventend' ).datetimepicker();
        }
);
