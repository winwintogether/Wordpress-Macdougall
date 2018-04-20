/**
 * Handles changing Slider Types, for example from Default to Instagram
 */
;(function ($, window, document, soliloquy_metabox ) {
	
	"use strict";
	
	//Dom Ready
    $( function() {
		
        // Change the radio checked option and fire the change event when a Gallery Type is clicked
        $( '#soliloquy-types-nav' ).on( 'click', 'li', function( e ) {
	        
	        //Return if this is the active class
			if ( $( this ).hasClass('soliloquy-active') ){
			
				return;
			
			}
            
            $( 'input[name="_soliloquy[type]"]', $( this ) ).prop( 'checked', true ).trigger( 'change' );

        });
        
        // Retrieve the settings HTML when the Gallery Type is changed, so the relevent options are displayed
        $( document ).on( 'change', 'input[name="_soliloquy[type]"]:radio', function( e ) {

            // Setup some vars
            var soliloquy_spinner      = $( '#soliloquy-tabs #soliloquy-tab-images .spinner' ),
                soliloquy_tab_settings = $( '#soliloquy-slider-main' );

            // Switch the Settings Metabox to the first tab (Images)
            $( 'a', $( '#soliloquy-settings-tabs li' ).first() ).trigger( 'click' );
			
            // Remove the content from the now displayed tab settings
            $( soliloquy_tab_settings ).html( '' );

            // Display the spinner, so the user knows something is happening
            $( soliloquy_spinner ).css( 'visibility', 'visible' );
            
            // Remove the soliloquy-active class from all Gallery Types
            $( 'li', $( this ).closest( '#soliloquy-types-nav' ) ).removeClass( 'soliloquy-active' );

            // Add the soliloquy-active class to the chosen Slider Type
            $( this ).closest( 'li' ).addClass( 'soliloquy-active' );
            
            // Make an AJAX call to get the content for the tab
            $.ajax( {
                type:       'post',
                url:        soliloquy_metabox.ajax,
                dataType:   'json',
                data: {
                    action:  'soliloquy_change_type',
                    post_id: soliloquy_metabox.id,
                    type:    $( this ).val(),
                    nonce:   soliloquy_metabox.change_nonce
                },
                success: function ( response ) {

                    // Inject the response into the tab settings area
                    $( soliloquy_tab_settings ).html( response.html );

                    // Fire an event to tell Addons that the Gallery Type has changed.
                    // (e.g. Featured Content Addon uses this to initialize some JS with the DOM).
                    $( document ).trigger( 'soliloquyType', response );

                    // Hide the spinner
                    $( soliloquy_spinner ).hide();
                    
                },
                error: function ( textStatus, errorThrown ) {

                    // Inject the error message into the tab settings area
                    $( soliloquy_tab_settings ).html( '<div class="error"><p>' + textStatus.responseText + '</p></div>' );

                    // Hide the spinner
                    $( soliloquy_spinner ).hide();

                }
            } );

        } );  

    } );
    
})( jQuery , window, document, soliloquy_metabox );