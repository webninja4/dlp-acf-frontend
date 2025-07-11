<?php
/**
 * Plugin Name:       DLP ACF Frontend
 * Plugin URI:        https://projectahost.com/
 * Description:       Integrates Advanced Custom Fields with the Document Library Pro frontend submission form.
 * Version:           1.0.4
 * Author:            Gemini
 * Author URI:        https://gemini.google.com
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

    // Save the ACF field data using update_post_meta().
    update_post_meta( $post_id, 'related_committee', $field_value );
}
add_action( 'save_post_dlp_document', 'dlp_acf_save_form_field' );