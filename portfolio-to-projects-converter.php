<?php
/**
 * Plugin Name: Portfolio to Projects Converter
 * Plugin URI: http://wordpress.org/plugins/portfolio-to-projects-converter/
 * Description: Converts Portfolio items into Project items.
 * Version: 1.0.1
 * Author: Matty, Jeffikus
 * Author URI: http://www.woothemes.com/
 * Requires at least: 3.8.1
 * Tested up to: 3.9.1
 *
 * Text Domain: portfolio-to-projects-converter
 * Domain Path: /languages/
 *
 * @package Portfolio_to_Projects_Converter
 * @category Core
 * @author Matty
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Returns the main instance of Portfolio_to_Projects_Converter to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Portfolio_to_Projects_Converter
 */
function Portfolio_to_Projects_Converter() {
	return Portfolio_to_Projects_Converter::instance();
} // End Portfolio_to_Projects_Converter()

Portfolio_to_Projects_Converter();

/**
 * Main Portfolio_to_Projects_Converter Class
 *
 * @class Portfolio_to_Projects_Converter
 * @version	1.0.0
 * @since 1.0.0
 * @package	Portfolio_to_Projects_Converter
 * @author Matty
 */
final class Portfolio_to_Projects_Converter {
	/**
	 * Portfolio_to_Projects_Converter The single instance of Portfolio_to_Projects_Converter.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $token;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $version;

	/**
	 * The admin object.
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $admin;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct () {
		$this->token 			= 'portfolio-to-projects-converter';
		$this->plugin_url 		= plugin_dir_url( __FILE__ );
		$this->plugin_path 		= plugin_dir_path( __FILE__ );
		$this->version 			= '1.0.0';

		register_activation_hook( __FILE__, array( $this, 'install' ) );

		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		add_action( 'plugins_loaded', array( $this, 'init' ) );
	} // End __construct()

	/**
	 * Initialise the plugin.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function init () {
		// Load the admin class, of we're in the admin.
		if ( is_admin() ) {
			require_once( 'classes/class-portfolio-to-projects-converter-admin.php' );
			$this->admin = new Portfolio_to_Projects_Converter_Admin();
		}
	} // End init()

	/**
	 * Main Portfolio_to_Projects_Converter Instance
	 *
	 * Ensures only one instance of Portfolio_to_Projects_Converter is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Portfolio_to_Projects_Converter()
	 * @return Main Portfolio_to_Projects_Converter instance
	 */
	public static function instance () {
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	} // End instance()

	/**
	 * Load the localisation file.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'portfolio-to-projects-converter', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	} // End load_plugin_textdomain()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	} // End __wakeup()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install()

	/**
	 * Log the plugin version number.
	 * @access  private
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		// Log the version number.
		update_option( $this->_token . '_version', $this->version );
	} // End _log_version_number()
} // End Class
?>
