<?php
/**
 * Plugin Name:       DLP ACF Frontend
 * Plugin URI:        https://projectahost.com/
 * Description:       Integrates Advanced Custom Fields with the Document Library Pro frontend submission form.
 * Version:           1.0.5
 * Author:            Paul Steele | Project A, Inc.
 * Author URI:        https://projecta.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       dlp-acf-frontend
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Enqueue ACF scripts and styles.
 */
function dlp_acf_enqueue_scripts() {
    if ( is_singular() && has_shortcode( get_the_content(), 'dlp_submission_form' ) ) {
        // Enqueue ACF's scripts and styles if ACF is available.
        if ( function_exists( 'acf_enqueue_scripts' ) ) {
            acf_enqueue_scripts();
        }
    }
}
add_action( 'wp_enqueue_scripts', 'dlp_acf_enqueue_scripts' );

/**
 * Display the 'related_committee' ACF field on the submission form.
 */
function dlp_acf_display_form_field() {
    // Ensure ACF is available.
    if ( ! function_exists( 'acf_get_field' ) || ! function_exists( 'acf_render_field_wrap' ) ) {
        return;
    }

    // Get the field object for 'related_committee'.
    $field = acf_get_field( 'related_committee' );

    // Ensure the field exists.
    if ( ! $field ) {
        return;
    }

    // Render the field wrapper. This will output the HTML for the field.
    acf_render_field_wrap( $field );
}
add_action( 'dlp_before_submission_form', 'dlp_acf_display_form_field' );

/**
 * Save the ACF field data when the main form is submitted.
 *
 * This function is hooked into the `dlp_document_submitted` action, which is
 * triggered by Document Library Pro after a document has been successfully created.
 *
 * @param int|\Barn2\Plugin\Document_Library_Pro\Document $document The ID of the new document or the Document object.
 */
/**
 * Save the ACF field data when the main form is submitted.
 *
 * This function is hooked into the `save_post_dlp_document` action, which is
 * triggered by Document Library Pro after a document has been successfully created.
 *
 * @param int $document_id The ID of the new document.
 */
function dlp_acf_save_form_field( $document_id ) {
    // Only act on the intended post type.
    if ( 'dlp_document' !== get_post_type( $document_id ) ) {
        return;
    }

    // Guard against autosaves/revisions.
    if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $document_id ) ) {
        return;
    }

    // Bail out if there's no ACF data for our field key.
    if ( ! isset( $_POST['acf']['field_684b598f4cb23'] ) ) {
        return;
    }

    // Verify either the DLP frontend nonce (for frontend submissions) or user capability (for admin/editor saves).
    $dlp_nonce_is_valid = false;
    if ( isset( $_POST['dlp_frontend_nonce'] ) ) {
        $dlp_nonce_is_valid = wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dlp_frontend_nonce'] ) ), 'dlp_frontend_submission' );
    }
    if ( ! $dlp_nonce_is_valid && ! current_user_can( 'edit_post', $document_id ) ) {
        return;
    }

    // Get and sanitize the 'related_committee' field value. Expect an array of post IDs for a relationship field.
    $raw_value   = wp_unslash( $_POST['acf']['field_684b598f4cb23'] );
    $field_value = array_filter( array_map( 'intval', (array) $raw_value ) );

    if ( function_exists( 'update_field' ) ) {
        update_field( 'field_684b598f4cb23', $field_value, $document_id );
    } else {
        // Fallback: store as post meta if ACF isn't available for some reason.
        update_post_meta( $document_id, 'related_committee', $field_value );
    }
}
add_action( 'save_post_dlp_document', 'dlp_acf_save_form_field' );

/**
 * Change form field labels.
 *
 * @param string $translated_text The translated text.
 * @param string $text            The original text.
 * @param string $domain          The text domain.
 * @return string The modified translated text.
 */
function dlp_acf_change_form_labels( $translated_text, $text, $domain ) {
    if ( ! is_admin() ) {
        switch ( $text ) {
            case 'Excerpt':
                return 'Brief Summary (10-15 words)';
            case 'Content':
                return 'Long Description';
        }
    }
    return $translated_text;
}
add_filter( 'gettext', 'dlp_acf_change_form_labels', 20, 3 );

//======================================================================
// FUNCTIONS MOVED FROM THEME
//======================================================================

/**
 * Format the 'related_committee' ACF field value for the 'dlp_document' post type.
 *
 * This function hooks into ACF and intercepts the 'related_committee' field
 * value, but only for the 'dlp_document' custom post type. It converts the
 * field's value from an array of post objects into a comma-separated string of
 * post titles. This prevents the Document Library Pro plugin from causing an
 * "Array to string conversion" error when it tries to display the field on the
 * singular document page.
 */
add_filter( 'acf/format_value/name=related_committee', function( $value, $post_id, $field ) {
    // Only format for our CPT.
    if ( 'dlp_document' !== get_post_type( $post_id ) ) {
        return $value;
    }

    if ( ! is_array( $value ) ) {
        return $value;
    }

    $committee_names = [];
    foreach ( $value as $item ) {
        // Support both post objects and IDs.
        if ( is_object( $item ) && isset( $item->ID ) ) {
            $title = get_the_title( (int) $item->ID );
        } elseif ( is_numeric( $item ) ) {
            $title = get_the_title( (int) $item );
        } elseif ( is_object( $item ) && isset( $item->post_title ) ) {
            $title = (string) $item->post_title;
        } else {
            $title = '';
        }

        $title = sanitize_text_field( (string) $title );
        if ( $title !== '' ) {
            $committee_names[] = $title;
        }
    }

    return implode( ', ', $committee_names );
}, 10, 3 );

// Hide classic editor custom field on document singular
add_filter( 'document_library_pro_custom_fields', function( $custom_fields_list, $post_id ) {
    // Delete an unwanted custom field from the custom fields list.
    unset( $custom_fields_list['classic-editor-remember'] );
    return $custom_fields_list;
}, 10, 2 );