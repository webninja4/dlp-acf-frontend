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
        // Enqueue ACF's scripts and styles.
        acf_enqueue_scripts();
    }
}
add_action( 'wp_enqueue_scripts', 'dlp_acf_enqueue_scripts' );

/**
 * Display the 'related_committee' ACF field on the submission form.
 */
function dlp_acf_display_form_field() {
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
    // Bail out if there's no ACF data for our field key.
    if ( ! isset( $_POST['acf']['field_684b598f4cb23'] ) ) {
        return;
    }

    // Get the post ID.
    $post_id = $document_id;

    // Ensure we have a valid post ID.
    if ( ! $post_id ) {
        return;
    }

    // Get the value of the 'related_committee' field from the submitted data using its field key.
    $field_value = $_POST['acf']['field_684b598f4cb23'];

    // Save the ACF field data using update_field().
    update_field( 'field_684b598f4cb23', $field_value, $post_id );
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
    // Check if the current post is the correct CPT and the value is an array.
    if ( get_post_type( $post_id ) === 'dlp_document' && is_array( $value ) ) {
        $committee_names = array_map( function( $post_object ) {
            // Ensure it's a post object with a title.
            if ( is_object( $post_object ) && isset( $post_object->post_title ) ) {
                return $post_object->post_title;
            }
            return null;
        }, $value );

        // Remove any null values and return a comma-separated string.
        return implode( ', ', array_filter( $committee_names ) );
    }

    // Return the original value if it's not a dlp_document or not an array.
    return $value;
}, 10, 3 );

// Hide classic editor custom field on document singular
add_filter( 'document_library_pro_custom_fields', function( $custom_fields_list, $post_id ) {
    // Delete an unwanted custom field from the custom fields list.
    unset( $custom_fields_list['classic-editor-remember'] );
    return $custom_fields_list;
}, 10, 2 );