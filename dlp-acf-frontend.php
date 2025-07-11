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
 * Enqueue ACF scripts and styles, and our custom script.
 */
function dlp_acf_enqueue_scripts() {
    if ( is_singular() && has_shortcode( get_the_content(), 'dlp_submission_form' ) ) {
        // Enqueue ACF's scripts and styles.
        acf_form_head();

        // Enqueue our custom script to prevent ACF from hijacking the form submission.
        wp_enqueue_script(
            'dlp-acf-frontend',
            plugin_dir_url( __FILE__ ) . 'assets/js/dlp-acf-frontend.js',
            ['jquery'],
            '1.0.0',
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'dlp_acf_enqueue_scripts' );


/**
 * Display the 'related_committee' ACF field on the submission form.
 * We use acf_form() to ensure the field renders correctly with its associated JS.
 */
function dlp_acf_display_form_field() {
    // Configuration for the ACF form.
    $options = [
        'post_id' => 'new_post',
        'new_post' => [
            'post_type'   => 'dlp_document',
            'post_status' => 'publish'
        ],
        // Specify the field to display by its name.
        'fields' => ['related_committee'],
        // We don't want ACF to render the <form> tag or submit button.
        'form' => false,
        'submit_value' => false,
    ];

    // Output the ACF form, wrapped for JS targeting.
    echo '<div id="dlp-acf-fields-wrapper" style="display:none;">';
    acf_form( $options );
    echo '</div>';
}
add_action( 'dlp_before_submission_form', 'dlp_acf_display_form_field' );

/**
 * Save the ACF field data when the main form is submitted.
 *
 * @param \Barn2\Plugin\Document_Library_Pro\Document $document The document object that was just created.
 */
function dlp_acf_save_form_field( $document_id ) {
    if ( function_exists( 'acf_save_post' ) && ! empty( $_POST['acf'] ) ) {
        acf_save_post( $document_id );
    }
}
add_action( 'save_post_dlp_document', 'dlp_acf_save_form_field' );