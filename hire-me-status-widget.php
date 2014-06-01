<?php
/*
 * Plugin Name: Hire Me Status Widget
 * Plugin URI: http://wordpress.org/plugins/hire-me-status-widget
 * Description: Easily display if you are available for hire using this widget. Useful for freelance developers like me.
 * Version: 1.0.0
 * Author: Sebastien Dumont
 * Author URI: http://www.sebastiendumont.com
 * Requires at least: 3.8
 * Tested up to: 3.9.1
 *
 * Text Domain: hire-me-status-widget
 * Domain Path: languages
 * Network: false
 *
 * Copyright: (c) 2014 Sebastien Dumont. (mailme@sebastiendumont.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Hire_Me_Status_Widget
 * @author Sebastien Dumont
 * @category Core
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Hire_Me_Status_Widget' ) ) {

/**
 * Main Hire Me Status Widget Class
 *
 * @class Hire_Me_Status_Widget
 * @version 1.0.0
 */
final class Hire_Me_Status_Widget {

	// Slug
	const slug = 'hire_me_status_widget';

	// Text Domain
	const text_domain = 'hire-me-status-widget';

	/**
	 * The Plug-in name.
	 *
	 * @var string
	 */
	public $name = "Hire Me Status Widget";

	/**
	 * The Plug-in version.
	 *
	 * @var string
	 */
	public $version = "1.0.0";

	/**
	 * The WordPress version the plugin requires minumum.
	 *
	 * @var string
	 */
	public $wp_version_min = "3.8";

	/**
	 * The single instance of the class
	 *
	 * @var Plugin Name
	 */
	protected static $_instance = null;

	/**
	 * Main Hire Me Status Widget Instance
	 *
	 * Ensures only one instance of Hire Me Status Widget is loaded or can be loaded.
	 *
	 * @access public static
	 * @see Hire_Me_Status_Widget()
	 * @return Hire Me Status Widget - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {
		// Define constants
		$this->define_constants();

		// Check plugin requirements
		$this->check_requirements();

		// Hooks
		add_action( 'widgets_init', array( &$this, 'include_widget' ) );
		add_action( 'init', array( &$this, 'init_hire_me_status_widget' ), 0 );
	}

	/**
	 * Define Constants
	 *
	 * @access private
	 */
	private function define_constants() {
		define( 'HIRE_ME_STATUS_WIDGET', $this->name );
		define( 'HIRE_ME_STATUS_WIDGET_FILE', __FILE__ );
		define( 'HIRE_ME_STATUS_WIDGET_VERSION', $this->version );
		define( 'HIRE_ME_STATUS_WIDGET_WP_VERSION_REQUIRE', $this->wp_version_min );
		define( 'HIRE_ME_STATUS_WIDGET_PAGE', str_replace('_', '-', self::slug) );
	}

	/**
	 * Checks that the WordPress setup meets the plugin requirements.
	 *
	 * @access private
	 * @global string $wp_version
	 * @return boolean
	 */
	private function check_requirements() {
		global $wp_version;

		if (!version_compare($wp_version, HIRE_ME_STATUS_WIDGET_WP_VERSION_REQUIRE, '>=')) {
			add_action('admin_notices', array( &$this, 'display_req_notice' ) );
			return false;
		}

		return true;
	}

	/**
	 * Display the requirement notice.
	 *
	 * @access static
	 */
	static function display_req_notice() {
		echo '<div id="message" class="error"><p><strong>';
		echo sprintf( __('Sorry, %s requires WordPress ' . HIRE_ME_STATUS_WIDGET_WP_VERSION_REQUIRE . ' or higher. Please upgrade your WordPress setup', 'hire-me-status-widget'), HIRE_ME_STATUS_WIDGET );
		echo '</strong></p></div>';
	}

	/**
	 * Include widget.
	 *
	 * @access public
	 * @return void
	 */
	public function include_widget() {
		include_once( 'includes/widget.php' );
	}

	/**
	 * Runs when the plugin is initialized.
	 *
	 * @access public
	 */
	public function init_hire_me_status_widget() {
		// Set up localisation
		$this->load_plugin_textdomain();
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any 
	 * following ones if the same translation is present.
	 *
	 * @access public
	 * @return void
	 */
	public function load_plugin_textdomain() {
		// Set filter for plugin's languages directory
		$lang_dir = dirname( plugin_basename( HIRE_ME_STATUS_WIDGET_FILE ) ) . '/languages/';
		$lang_dir = apply_filters( 'hire_me_status_widget_languages_directory', $lang_dir );

		// Traditional WordPress plugin locale filter
		$locale = apply_filters( 'plugin_locale',  get_locale(), self::text_domain );
		$mofile = sprintf( '%1$s-%2$s.mo', self::text_domain, $locale );

		// Setup paths to current locale file
		$mofile_local  = $lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/' . self::text_domain . '/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/hire-me-status-widget/ folder
			load_textdomain( self::text_domain, $mofile_global );
		}
		elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/hire-me-status-widget/languages/ folder
			load_textdomain( self::text_domain, $mofile_local );
		}
		else {
			// Load the default language files
			load_plugin_textdomain( self::text_domain, false, $lang_dir );
		}
	}

	/** Helper functions ******************************************************/

	/**
	 * Get the plugin url.
	 *
	 * @access public
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @access public
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

} // end class

} // end if class exists

/**
 * Returns the main instance of Hire_Me_Status_Widget 
 * to prevent the need to use globals.
 *
 * @return Hire_Me_Status_Widget()
 */
function Hire_Me_Status_Widget() {
	return Hire_Me_Status_Widget::instance();
}

// Global for backwards compatibility.
$GLOBALS['hire-me-status-widget'] = Hire_Me_Status_Widget();

?>