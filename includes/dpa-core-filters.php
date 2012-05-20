<?php
/**
 * Achievements filters
 *
 * @package Achievements
 * @subpackage Filters
 *
 * This file contains the filters that are used throughout Achievements. They are
 * consolidated here to make searching for them easier, and to help developers
 * understand at a glance the order in which things occur.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Attach Achievements to WordPress
 *
 * Achievements uses its own internal actions to help aid in third-party plugin
 * development, and to limit the amount of potential future code changes when
 * updates to WordPress core occur.
 *
 * These actions exist to create the concept of 'plugin dependencies'. They
 * provide a safe way for plugins to execute code *only* when Achievements is
 * installed and activated, without needing to do complicated guesswork.
 */
//add_filter( 'request',                 'dpa_request',            10    );
//add_filter( 'template_include',        'dpa_template_include',   10    );
//add_filter( 'wp_title',                'dpa_title',              10, 3 );
//add_filter( 'body_class',              'dpa_body_class',         10, 2 );
add_filter( 'map_meta_cap',            'dpa_map_meta_caps',      10, 4 );
//add_filter( 'allowed_themes',          'dpa_allowed_themes',     10    );
//add_filter( 'redirect_canonical',      'dpa_redirect_canonical', 10    );
//add_filter( 'login_redirect',          'dpa_redirect_login',     2,  3 );
//add_filter( 'logout_url',              'dpa_logout_url',         2,  2 );
?>