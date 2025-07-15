/**
 * Moves ACF fields into the DLP submission form and re-initializes ACF.
 */
jQuery( document ).ready( function( $ ) {
    console.log( 'DLP ACF Frontend JS: Script loaded.' );

    const targetNode = document.getElementById( 'dlp-submit-form' );

    // If the DLP form is not immediately available, use a MutationObserver.
    if ( ! targetNode ) {
        console.log( 'DLP ACF Frontend JS: dlp-submit-form not found immediately. Initializing MutationObserver.' );
        const observer = new MutationObserver( function( mutationsList, observer ) {
            for ( const mutation of mutationsList ) {
                if ( mutation.type === 'childList' ) {
                    const dlpForm = document.getElementById( 'dlp-submit-form' );
                    if ( dlpForm ) {
                        console.log( 'DLP ACF Frontend JS: dlp-submit-form found by MutationObserver.' );
                        moveAcfFields( $ );
                        observer.disconnect(); // Stop observing once the form is found.
                        break;
                    }
                }
            }
        } );

        // Observe the body for changes in its child list.
        observer.observe( document.body, { childList: true, subtree: true } );
    } else {
        console.log( 'DLP ACF Frontend JS: dlp-submit-form found immediately.' );
        // If the form is already in the DOM, move fields immediately.
        moveAcfFields( $ );
    }

    function moveAcfFields( $ ) {
        console.log( 'DLP ACF Frontend JS: moveAcfFields function called.' );
        const $dlpForm = $( '#dlp-submit-form' );
        const $acfFieldsWrapper = $( '#dlp-acf-fields-wrapper' );

        console.log( 'DLP ACF Frontend JS: $dlpForm length:', $dlpForm.length );
        console.log( 'DLP ACF Frontend JS: $acfFieldsWrapper length:', $acfFieldsWrapper.length );

        if ( $dlpForm.length && $acfFieldsWrapper.length ) {
            // Find a suitable place to insert the ACF fields. For example, before the submit button.
            const $submitButton = $dlpForm.find( 'button[type="submit"]' ).first();

            if ( $submitButton.length ) {
                $acfFieldsWrapper.insertBefore( $submitButton );
                console.log( 'DLP ACF Frontend JS: ACF fields wrapper inserted before submit button.' );
            } else {
                // Fallback: append to the form if no submit button is found.
                $dlpForm.append( $acfFieldsWrapper );
                console.log( 'DLP ACF Frontend JS: ACF fields wrapper appended to form (no submit button found).' );
            }

            // Make the ACF fields visible.
            $acfFieldsWrapper.show();
            console.log( 'DLP ACF Frontend JS: ACF fields wrapper made visible.' );

            

            // Prevent ACF from creating its own form submission handler.
            // This is crucial to avoid double submissions.
            $acfFieldsWrapper.find( 'input[name="_acf_form"]' ).remove();
            console.log( 'DLP ACF Frontend JS: Removed _acf_form hidden input.' );
        } else {
            console.log( 'DLP ACF Frontend JS: Could not find both $dlpForm and $acfFieldsWrapper. Fields not moved.' );
        }
    }
} );