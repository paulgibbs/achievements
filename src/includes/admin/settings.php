<?php
/**
 * Achievements admin settings
 *
 * @package Achievements
 * @subpackage AdminSettings
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The main settings page
 *
 * @since Achievements (3.6)
 */
function dpa_admin_settings() {
?>

	<div class="wrap">
		<h2><?php esc_html_e( 'Achievements&#8217; Settings', 'achievements' ) ?></h2>

		<form action="options.php" method="post">
			<?php settings_fields( 'achievements' ); ?>
			<?php do_settings_sections( 'achievements' ); ?>

			<p class="submit">
				<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'achievements' ); ?>" />
			</p>
		</form>
	</div>

<?php
}

/**
 * Get the admin settings sections.
 *
 * @return array
 * @since Achievements (3.6)
 */
function dpa_admin_get_settings_sections() {
	return (array) apply_filters( 'dpa_admin_get_settings_sections', array(
		'dpa_settings_pagination' => array(
			'callback' => 'dpa_admin_setting_callback_pagination_section',
			'page'     => 'reading',
			'title'    => _x( 'Pagination', 'admin settings section name', 'achievements' ),
		),
		'dpa_settings_slugs' => array(
			'callback' => 'dpa_admin_setting_callback_slugs_section',
			'page'     => 'permalink',
			'title'    => _x( 'Permalinks', 'admin settings section name', 'achievements' ),
		),
		'dpa_settings_templates' => array(
			'callback' => 'dpa_admin_setting_callback_templates_section',
			'page'     => 'general',
			'title'    => _x( 'Templates', 'admin settings section name', 'achievements' ),
		),
	) );
}

/**
 * Get all of the settings fields.
 *
 * @return type
 * @since Achievements (3.6)
 */
function dpa_admin_get_settings_fields() {
	return (array) apply_filters( 'dpa_admin_get_settings_fields', array(

		// Templates section
		'dpa_settings_templates' => array(

			// Theme package setting
			'_dpa_theme_package_id' => array(
				'args'              => array(),
				'callback'          => 'dpa_admin_setting_callback_theme_package_id',
				'sanitize_callback' => 'dpa_admin_setting_validate_theme_package_id',
				'title'             => _x( 'Template version', 'admin settings name', 'achievements' ),
			)
		),

		// Pagination section
		'dpa_settings_pagination' => array(

			// Achievements per page setting
			'_dpa_achievements_per_page' => array(
				'args'              => array(),
				'callback'          => 'dpa_admin_setting_callback_achievements_per_page',
				'sanitize_callback' => 'absint',
				'title'             => _x( 'Achievements', 'admin settings name for pagination', 'achievements' ),
			),

			// Achievements per RSS page setting
			'_dpa_achievements_per_rss_page' => array(
				'args'              => array(),
				'callback'          => 'dpa_admin_setting_callback_achievements_per_rss_page',
				'sanitize_callback' => 'absint',
				'title'             => _x( 'RSS feed', 'admin settings name for pagination', 'achievements' ),
			),
		),

		// Slugs
		'dpa_settings_slugs' => array(

			// Root slug setting
			'_dpa_root_slug' => array(
				'args'              => array(),
				'callback'          => 'dpa_admin_setting_callback_root_slug',
				'sanitize_callback' => 'sanitize_title',
				'title'             => _x( 'Archive', 'admin settings name', 'achievements' ),
			),

			// Single achievement slug setting
			'_dpa_singular_root_slug' => array(
				'args'              => array(),
				'callback'          => 'dpa_admin_setting_callback_achievement_slug',
				'sanitize_callback' => 'sanitize_title',
				'title'             => _x( 'Single item', 'admin settings opton name', 'achievements' ),
			),
		)
	) );
}

/**
 * Get settings fields by section.
 *
 * @param string $section_id
 * @return array
 * @since Achievements (3.6)
 */
function dpa_admin_get_settings_fields_for_section( $section_id  ) {
	$fields = dpa_admin_get_settings_fields();
	$retval = isset( $fields[$section_id] ) ? $fields[$section_id] : array();

	return (array) apply_filters( 'dpa_admin_get_settings_fields_for_section', $retval, $section_id );
}

/**
 * Disable a settings field if the value is forcibly set in Achievements' global options array.
 *
 * @param string $option_key
 * @since Achievements (3.6)
 */
function dpa_maybe_admin_setting_disabled( $option_key ) {
	disabled( isset( achievements()->options[$option_key] ) );
}


/**
 * Theme compatibility templates section header.
 *
 * @since Achievements (3.6)
 */
function dpa_admin_setting_callback_templates_section() {
?>

	<p><?php _e( 'Each release, the templates that come with Achievements are improved. If you&#8217;ve updated the plugin and prefer the templates from an older version, use this setting to tell Achievements to load those older templates.', 'achievements' ); ?></p>

<?php
}

/**
 * Theme compatibility templates settings.
 *
 * This is used as a mechanisim for facilitating significant/breaking changes to existing templates where we want to try to maintain backwards compatibility.
 *
 * @since Achievements (3.6)
 */
function dpa_admin_setting_callback_theme_package_id() {
	$theme_options   = '';
	$current_package = dpa_get_theme_package_id( 'default' );

	foreach ( (array) achievements()->theme_compat->packages as $id => $theme ) {
		$theme_options .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $id ), selected( $theme->id, $current_package, false ), esc_html( $theme->name ) );
	}

	if ( $theme_options ) : ?>
		<select name="_dpa_theme_package_id" id="_dpa_theme_package_id" <?php dpa_maybe_admin_setting_disabled( '_dpa_theme_package_id' ); ?>><?php echo $theme_options ?></select>
	<?php endif;
}

/**
 * Settings API validation callback function for the _dpa_theme_package_id setting.
 *
 * @param string $new_package User-supplied value to check
 * @return string
 * @see dpa_admin_get_settings_fields()
 * @since Achievements (3.6)
 */
function dpa_admin_setting_validate_theme_package_id( $new_package ) {
	$valid_package_ids = array_keys( achievements()->theme_compat->packages );
	$new_package       = in_array( $new_package, $valid_package_ids ) ? $new_package : 'default';

	return apply_filters( 'dpa_admin_setting_validate_theme_package_id', $new_package );
}


/**
 * Pagination section header.
 *
 * @since Achievements (3.6)
 */
function dpa_admin_setting_callback_pagination_section() {
?>

	<p><?php _e( 'How many achievements do you want to show per page?', 'achievements' ); ?></p>

<?php
}

/**
 * Achievements per page setting field.
 *
 * @since Achievements (3.6)
 */
function dpa_admin_setting_callback_achievements_per_page() {
?>

	<input name="_dpa_achievements_per_page" id="_dpa_achievements_per_page" type="number" min="1" step="1" value="<?php echo (int) dpa_get_achievements_per_page(); ?>" class="small-text" required <?php dpa_maybe_admin_setting_disabled( '_dpa_achievements_per_page' ); ?> />
	<label for="_dpa_achievements_per_page"><?php _ex( 'per page', 'achievements pagination', 'achievements' ); ?></label>

<?php
}

/**
 * Achievements per RSS page setting field
 *
 * @since Achievements (3.6)
 */
function dpa_admin_setting_callback_achievements_per_rss_page() {
?>

	<input name="_dpa_achievements_per_rss_page" id="_dpa_achievements_per_rss_page" type="number" min="1" step="1" value="<?php echo (int) dpa_get_achievements_per_rss_page(); ?>" class="small-text" required <?php dpa_maybe_admin_setting_disabled( '_dpa_achievements_per_rss_page' ); ?> />
	<label for="_dpa_achievements_per_rss_page"><?php _ex( 'per page', 'achievements pagination', 'achievements' ); ?></label>

<?php
}


/**
 * URLs section header.
 *
 * @since Achievements (3.6)
 */
function dpa_admin_setting_callback_slugs_section() {
	if ( isset( $_GET['settings-updated'] ) && isset( $_GET['page'] ) ) {
		// Flush rewrite rules when this section is saved
		flush_rewrite_rules();
	}
?>

	<p><?php _e( 'Customise the URLs that you access Achievements&#8217; content on.', 'achievements' ); ?></p>

<?php
}

/**
 * Root slug field.
 *
 * @since Achievements (3.6)
 */
function dpa_admin_setting_callback_root_slug() {
?>

	<input name="_dpa_root_slug" id="_dpa_root_slug" type="text" class="regular-text code" value="<?php echo esc_attr( dpa_get_root_slug() ); ?>"<?php dpa_maybe_admin_setting_disabled( '_dpa_root_slug' ); ?> required />

<?php
}

/**
 * Single achievement slug field.
 *
 * @since Achievements (3.6)
 */
function dpa_admin_setting_callback_achievement_slug() {
?>

	<input name="_dpa_singular_root_slug" id="_dpa_singular_root_slug" type="text" class="regular-text code" value="<?php echo esc_attr( dpa_get_singular_root_slug() ); ?>"<?php dpa_maybe_admin_setting_disabled( '_dpa_singular_root_slug' ); ?> required />

<?php
}
