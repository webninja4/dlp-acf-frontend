# Project: DLP ACF Frontend

This document provides a technical overview of the `dlp-acf-frontend` plugin for future development and maintenance.

## Core Functionality

The plugin integrates Advanced Custom Fields (ACF) with the Document Library Pro (DLP) frontend submission form. Its main functions are:

1.  **`dlp_acf_enqueue_scripts()`**: Enqueues the necessary ACF scripts and styles on pages that contain the `[dlp_submission_form]` shortcode.
2.  **`dlp_acf_display_form_field()`**: Renders the "Related Committee" (`related_committee`) ACF relationship field on the DLP submission form using the `dlp_before_submission_form` action hook.
3.  **`dlp_acf_save_form_field( $document_id )`**: Saves the submitted "Related Committee" value. This function is hooked into the `save_post_dlp_document` action.
4.  **`dlp_acf_change_form_labels( $translated_text, $text, $domain )`**: Modifies the labels for the "Excerpt" and "Content" fields on the frontend submission form to "Brief Summary (10-15 words)" and "Long Description" respectively, using the `gettext` filter.

## Development Journey & Key Decisions

### Challenge 1: Saving the ACF Relationship Field

*   **Problem**: The "Related Committee" field, which is an ACF relationship field, was not saving correctly from the frontend form. The value was stored as a serialized PHP array (e.g., `a:1:{i:0;s:3:"211";}`) instead of a proper post relationship, preventing it from being displayed correctly on the frontend.
*   **Incorrect Approach**: The initial implementation used the standard WordPress `update_post_meta()` function. This function is not sufficient for complex ACF fields like relationships, as it doesn't handle the necessary data formatting.
*   **Correct Solution**: The issue was resolved by replacing `update_post_meta()` with ACF's dedicated `update_field()` function. This function correctly handles the data for all ACF field types, including relationships. The final implementation uses `update_field( 'field_684b598f4cb23', $field_value, $post_id )` to save the data, referencing the field's unique key.

### Challenge 2: Modifying Form Field Labels

*   **Problem**: The default labels for "Excerpt" and "Content" on the DLP submission form needed to be changed to be more descriptive for users.
*   **Initial Research**: An initial search for a specific filter within the Document Library Pro plugin to modify these labels did not yield a direct solution.
*   **Correct Solution**: The WordPress core `gettext` filter was used as a flexible and reliable alternative. This filter allows for the dynamic modification of any translatable text string. A function was created to check for the specific strings "Excerpt" and "Content" on non-admin pages and replace them with the desired labels.
