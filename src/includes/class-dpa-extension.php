<?php
/**
 * Base class for adding support for your plugin to Achievements.
 *
 * To add support for your plugin to Achievements, you need to create a new
 * class derived from either {@link DPA_Extension} or {@link DPA_CPT_Extension}.
 *
 * We need to add the actions you are supporting into the dpa_event taxonomy.
 * We take care of initial "installation" for you, so you'll only need to
 * implement any update logic in your {@link DPA_Extension::do_update} method
 * as/when required.
 *
 * To load an extension, in a function hooked to the 'dpa_ready' action,
 * instantiate your class and store it in the main achievements object, e.g.
 *
 * achievements()->extensions->your_plugin = new Your_DPA_Extension_Class();
 *
 * That's all. We takes care of everything else.
 *
 * @package Achievements
 * @subpackage CoreClasses
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add support to Achievements for your plugin using this class. It's used to
 * store information about the plugin and actions that you are adding support for.
 *
 * The objects that you store in achievements()->extensions need to be derived
 * from this class.
 *
 * If the action which you are adding support for is a WordPress core custom
 * post type action, use {@link DPA_CPT_Extension} rather than this class.
 *
 * @since Achievements (3.0)
 */
abstract class DPA_Extension {
	/**
	 * Define the WordPress actions that Achievements needs to react to as an array with key/value pairs. For example:
	 *
	 * array(
	 *   'action_name' => 'description',
	 *
	 *   // For example
	 *   'publish_post' => __( 'The user publishes a post or page.', 'your_plugin' ),
	 *   'trashed_post' => __( 'The user trashes a post or page.',   'your_plugin' ),
	 * )
	 *
	 * @link http://codex.wordpress.org/Plugin_API#Actions
	 * @see DPA_Extension::get_actions();
	 * @since Achievements (3.0)
	 */
	protected $actions         = array();

	/**
	 * Details of the contributors of this plugin. You should set this to an array with these key/value pairs:
	 *
	 * array(
	 *   array(
	 *     'name'         => '',
	 *     'gravatar_url' => '',
	 *     'profile_url'  => '',
	 *  ),
	 *
	 *   // For example
	 *   array(
	 *     'name'         => 'Paul Gibbs',
	 *     'gravatar_url' => 'http://www.gravatar.com/avatar/3bc9ab796299d67ce83dceb9554f75df',
	 *     'profile_url'  => 'http://profiles.wordpress.org/DJPaul'
	 *   ),
	 * )
	 * @see DPA_Extension::get_contributors()
	 * @since Achievements (3.0)
	 */
	protected $contributors    = array();

	/**
	 * Set this to a short description of your plugin.
	 *
	 * @see DPA_Extension::get_description();
	 * @since Achievements (3.0)
	 */
	protected $description     = '';

	/**
	 * Set this to an absolute path to your plugin's logo.
	 *
	 * MUST be local (e.g. on user's own site, rather than linking to your own site).
	 * MUST be 772×250px.
	 *
	 * @see DPA_Extension::get_image_url();
	 * @since Achievements (3.0)
	 */
	protected $image_url       = '';

	/**
	 * Set this to an absolute path a small version of your plugin's logo.
	 *
	 * MUST be local (e.g. on user's own site, rather than linking to your own site).
	 * MUST be 145x57px.
	 *
	 * @see DPA_Extension::get_image_url();
	 * @since Achievements (3.0)
	 */
	protected $small_image_url = '';

	/**
	 * Set this to the name of your plugin.
	 *
	 * @see DPA_Extension::get_name();
	 * @since Achievements (3.0)
	 */
	protected $name            = '';

	/**
	 * Set this to an absolute URL to a news RSS feed for this plugin. This may be your own website.
	 *
	 * @see DPA_Extension::get_rss_url();
	 * @since Achievements (3.0)
	 */
	protected $rss_url         = '';

	/**
	 * Set this to your plugin's svn.wp-plugins.org slug. e.g. http://svn.wp-plugins.org/[your_plugin_slug]/
	 * This is used for keying indexes and is also output on elements' class properties in the templates.
	 *
	 * @see DPA_Extension::get_id();
	 * @since Achievements (3.0)
	 */
	protected $id              = '';

	/**
	 * Set this to an integer representing the version of your extension (not the version of your plugin).
	 * This is used internally to detect if we need to run any installation or update routine for your
	 * plugin. For example, you might add a new action to support in a second version of your extension.
	 *
	 * The implementation of any updating handling is down to you.
	 *
	 * @see DPA_Extension::do_update();
	 * @see DPA_Extension::version();
	 * @since Achievements (3.0)
	 */
	protected $version         = 0;

	/**
	 * Set this to an absolute URL to your plugin's page on WordPress.org.
	 *
	 * @see DPA_Extension::wporg_url();
	 * @since Achievements (3.0)
	 */
	protected $wporg_url       = '';


	/**
	 * Returns details of actions from this plugin that Achievements can use.
	 *
	 * @return array
	 * @since Achievements (3.0)
	 */
	public function get_actions() {
		return $this->actions;
	}

	/**
	 * Returns nested array of key/value pairs for each contributor to this plugin (name, gravatar URL, profile URL).
	 *
	 * @return array
	 * @since Achievements (3.0)
	 */
	public function get_contributors() {
		return $this->contributors;
	}

	/**
	 * Return description
	 *
	 * @return string
	 * @since Achievements (3.0)
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Return absolute URL to plugin image.
	 *
	 * @return string
	 * @since Achievements (3.0)
	 */
	public function get_image_url() {
		return $this->image_url;
	}

	/**
	 * Return absolute URL to small size plugin image.
	 *
	 * @return string
	 * @since Achievements (3.0)
	 */
	public function get_small_image_url() {
		return $this->small_image_url;
	}

	/**
	 * Return plugin name
	 *
	 * @return string
	 * @since Achievements (3.0)
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Return absolute URL to a news RSS feed for this plugin.
	 *
	 * @return string
	 * @since Achievements (3.0)
	 */
	public function get_rss_url() {
		return $this->rss_url;
	}

	/**
	 * Return plugin identifier
	 *
	 * @return string
	 * @since Achievements (3.0)
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Return version number of your extension
	 *
	 * @return int
	 * @since Achievements (3.0)
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Return absolute URL to your plugin on WordPress.org
	 *
	 * @return string
	 * @since Achievements (3.0)
	 */
	public function get_wporg_url() {
		return $this->wporg_url;
	}

	/**
	 * Implement an update routine for your extension.
	 *
	 * Achievements adds your actions into the dpa_event taxonomy if it has no
	 * record of it in the "_dpa_extension_versions" site option. If the option already
	 * has a version number recorded, Achievements compares that to the value from
	 * {@link self::get_version}. If the extension reports a higher version number,
	 * then this method will be called.
	 *
	 * @param string $current_version
	 * @since Achievements (3.0)
	 */
	public function do_update( $current_version ) {}
}