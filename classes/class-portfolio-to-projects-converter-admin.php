<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Homepage_Control_Admin Class
 *
 * All functionality pertaining to the homepage control administration interface.
 *
 * @package WordPress
 * @subpackage Homepage_Control
 * @category Plugin
 * @author Matty
 * @since 1.0.0
 */
final class Portfolio_To_Projects_Converter_Admin {
	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $token;

	/**
	 * Keep track of the number of portfolio items.
	 * @var     int
	 * @access  private
	 * @since   1.0.0
	 */
	private $_portfolio_item_count;

	/**
	 * Keep track of the portfolio item IDs.
	 * @var     array
	 * @access  private
	 * @since   1.0.0
	 */
	private $_portfolio_item_ids;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct () {
		$this->token = 'portfolio-to-projects-converter';
		$this->_portfolio_item_ids = array();
		$this->_portfolio_item_count = null;

		// Register the admin screen.
		add_action( 'admin_menu', array( $this, 'register_settings_screen' ) );
	} // End __construct()

	/**
	 * Register the admin screen.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function register_settings_screen () {
		$this->_hook = add_submenu_page( 'tools.php', __( 'Portfolio to Projects', 'portfolio-to-projects-converter' ), __( 'Portfolio to Projects', 'portfolio-to-projects-converter' ), 'manage_options', 'portfolio-to-projects-converter', array( $this, 'settings_screen' ) );
	} // End register_settings_screen()

	/**
	 * Output the markup for the settings screen.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function settings_screen () {
?>
		<div class="wrap portfolio-to-projects-wrap">
			<h2><?php _e( 'Portfolio to Projects Converter', 'portfolio-to-projects-converter' ); ?></h2>
<?php
$count = $this->_get_portfolio_item_count();

if ( 0 < $count ) {
?>
			<p><?php printf( __( 'Number of portfolio items found: %s', 'portfolio-to-projects-converter' ), $count ); ?></p>
			<form method="post">
				<?php
					wp_nonce_field( 'portfolio-to-projects-converter', 'portfolio-to-projects-converter' );
					submit_button( __( 'Convert Portfolio Items', 'portfolio-to-projects-converter' ) );
				?>
			</form>
<?php
} else {
	echo wpautop( __( 'It looks like you\'ve converted all of your portfolio items. You\'re good to go!', 'portfolio-to-projects-converter' ) );
}
?>
		</div><!--/.wrap-->
<?php
	} // End settings_screen()

	/**
	 * Check if we have portfolio items or not.
	 * @access  private
	 * @since   1.0.0
	 * @return  void
	 */
	private function _has_portfolio_items () {
		// TODO
	} // End _has_portfolio_items()

	/**
	 * Retrieve the portfolio item IDs which haven't been converted.
	 * @access  private
	 * @since   1.0.0
	 * @return  void
	 */
	private function _get_portfolio_items () {
		// If we already have IDs, return those.
		if ( is_array( $this->_portfolio_item_ids ) && 0 < count( $this->_portfolio_item_ids ) ) {
			return $this->_portfolio_item_ids;
		}
		// Otherwise, query for the ID values and store them for later.
		$meta_query = array(); // TODO
		$args = array( 'post_type' => 'portfolio', 'limit' => intval( apply_filters( 'portfolio_to_projects_upper_limit', 50 ) ), 'meta_query' => $meta_query );
		$response = get_posts( $args );

		if ( ! is_wp_error( $response ) && 0 < count( $response ) ) {
			foreach ( $response as $k => $v ) {
				if ( isset( $v->ID ) && ! in_array( $v->ID, $this->_portfolio_item_ids ) ) {
					$this->_portfolio_item_ids[] = intval( $v->ID );
				}
			}
		}
	} // End _get_portfolio_items()

	/**
	 * Retrieve the number of portfolio items found in the current iteration.
	 * @access  private
	 * @since   1.0.0
	 * @return  void
	 */
	private function _get_portfolio_item_count () {
		if ( ! is_null( $this->_portfolio_item_count ) ) {
			return intval( $this->_portfolio_item_count );
		}
		$count = wp_count_posts( 'portfolio' );

		if ( ! is_int( $count ) ) {
			$count = 0;
		}

		$this->_portfolio_item_count = intval( $count );

		return $this->_portfolio_item_count;
	} // End _get_portfolio_item_count()
} // End Class
?>