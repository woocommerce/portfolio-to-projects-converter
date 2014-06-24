<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Portfolio_To_Projects_Converter_Admin Class
 *
 * All functionality pertaining to the Portfolio to Projects Converter administration interface.
 *
 * @package WordPress
 * @subpackage Portfolio_To_Projects_Converter
 * @category Plugin
 * @author Matty, Jeffikus
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
		$this->upper_limit = intval( apply_filters( 'portfolio_to_projects_upper_limit', 10 ) );

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
				$this->run_updates_page();
			} else {
				echo wpautop( __( 'It looks like you\'ve converted all of your portfolio items. You\'re good to go!', 'portfolio-to-projects-converter' ) ); ?>
				<p><a href="<?php echo admin_url('edit.php?post_type=project'); ?>"><?php _e( 'Create a new project', 'portfolio-to-projects-converter' ); ?></a>.</p>
				<?php
			} // End If Statement
			?>
		</div><!--/.wrap-->
		<?php
	} // End settings_screen()

	/**
	 * Retrieve the portfolio item IDs which haven't been converted.
	 * @access  private
	 * @since   1.0.0
	 * @return  array portfolio IDs
	 */
	private function _get_portfolio_items () {
		// If we already have IDs, return those.
		if ( is_array( $this->_portfolio_item_ids ) && 0 < count( $this->_portfolio_item_ids ) ) {
			return $this->_portfolio_item_ids;
		}
		// Otherwise, query for the ID values and store them for later.
		$meta_query = array( 'meta_key' => '_is_converted_to_project', 'meta_value' => 'true', 'meta_compare' => 'NOT EXISTS' );
		$args = array( 'post_type' => 'portfolio', 'limit' => intval( apply_filters( 'portfolio_to_projects_upper_limit', 10 ) ), 'meta_query' => $meta_query );
		$response = get_posts( $args );

		if ( ! is_wp_error( $response ) && 0 < count( $response ) ) {
			foreach ( $response as $k => $v ) {
				if ( isset( $v->ID ) && ! in_array( $v->ID, $this->_portfolio_item_ids ) ) {
					$this->_portfolio_item_ids[] = intval( $v->ID );
				} // End If Statement
			} // End For Loop
		} // End If Statement

		return $this->_portfolio_item_ids;
	} // End _get_portfolio_items()

	/**
	 * Retrieve the number of portfolio items found in the current iteration.
	 * @access  private
	 * @since   1.0.0
	 * @return  int count of portfolio items
	 */
	private function _get_portfolio_item_count () {
		if ( ! is_null( $this->_portfolio_item_count ) ) {
			return intval( $this->_portfolio_item_count );
		}
		$portfolio_total_count = wp_count_posts( 'portfolio' );
		$portfolio_total_count_obj = (array)$portfolio_total_count;
		$count = intval( array_sum( $portfolio_total_count_obj ) );

		if ( ! is_int( $count ) ) {
			$count = 0;
		} // End If Statement

		$this->_portfolio_item_count = intval( $count );

		// Get any existing copy of our transient data
		$transient_test = intval( get_transient( 'portfolio_to_project_count' ) );
		if ( false === $transient_test || '' === $transient_test || 0 >= $transient_test ) {
			// Check values are a match and update is not in progress
			if ( !isset( $_GET['action'] ) && $count !== $transient_test ) {
				set_transient( 'portfolio_to_project_count', $count );
			} // End If Statement
		} // End If Statement

		return $this->_portfolio_item_count;
	} // End _get_portfolio_item_count()

	/**
	 * run_updates_page HTML output for update screen
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function run_updates_page() {

		$count = intval( $this->_portfolio_item_count );
		// Only allow admins to load this page and run the update functions
		if( current_user_can( 'administrator' ) ) {

			if ( isset( $_GET['action'] ) && $_GET['action'] == 'update' && isset( $_GET['n'] ) && intval( $_GET['n'] ) >= 0 ) {

				// Setup the data variables
				$n = intval( $_GET['n'] );
				$done_processing = false;

				// Check for updates to run
					// Run the update function call
					$done_processing = $this->convert_portfolio_items_to_projects( $n );


				if ( ! $done_processing ) { ?>

					<h3><?php _e( 'Processing Updates......', 'portfolio-to-projects-converter' ); ?></h3>

					<p><?php _e( 'If your browser doesn&#8217;t start loading the next page automatically, click this link:', 'portfolio-to-projects-converter' ); ?>&nbsp;&nbsp;<a class="button" href="tools.php?page=portfolio-to-projects-converter&action=update&n=<?php echo ($n + intval( $this->upper_limit) ) ?>"><?php _e( 'Next', 'portfolio-to-projects-converter' ); ?></a></p>
					<script type='text/javascript'>
					<!--
					function converter_nextpage() {
						location.href = "tools.php?page=portfolio-to-projects-converter&action=update&n=<?php echo ( $n + intval( $this->upper_limit) ) ?>";
					}
					setTimeout( "converter_nextpage()", 250 );
					//-->
					</script><?php

				} else { ?>

					<p><strong><?php _e( 'Portfolio to Projects completed successfully!', 'portfolio-to-projects-converter' ); ?></strong></p>
					<p><a href="<?php echo admin_url('edit.php?post_type=project'); ?>"><?php _e( 'Create a new project', 'portfolio-to-projects-converter' ); ?></a>.</p>

				<?php } // End If Statement

			} else { ?>

				<p><?php printf( __( 'Number of portfolio items found: %s', 'portfolio-to-projects-converter' ), $count ); ?></p>
				<form method="post" action="tools.php?page=portfolio-to-projects-converter&action=update&n=0">
					<?php
						wp_nonce_field( 'portfolio-to-projects-converter', 'portfolio-to-projects-converter' );
						submit_button( __( 'Convert Portfolio Items', 'portfolio-to-projects-converter' ) );
					?>
				</form>

			<?php
			} // End If Statement

		} else { ?>

			<p><?php _e( 'You do not have permission to perform this function.', 'portfolio-to-projects-converter' ); ?></p>

		<?php } // End If Statement

	} // End run_updates_page()

	/**
	 * convert_portfolio_items_to_projects converts the portfolio items and all metadata to projects
	 *
	 * @access public
	 * @since  1.0.0
	 * @param  int $offset number of items to query
	 * @return boolean true or false success value
	 */
	public function convert_portfolio_items_to_projects( $offset = 0 ) {

		// The Query
		$meta_query = array( 'meta_key' => '_is_converted_to_project', 'meta_value' => 'true', 'meta_compare' => 'NOT EXISTS' );
		$args = array(	'post_type' 		=> 'portfolio',
						'numberposts' 		=> $this->upper_limit,
						'post_status'		=> 'any',
						'suppress_filters' 	=> 0,
						'meta_query'		=> $meta_query
						);
		$portfolio_items = get_posts( $args );

		$count_complete = $offset;

		foreach( $portfolio_items as $portfolio_item ) {

			// Update Post Type
			$post_object = array(	'ID'           => $portfolio_item->ID,
									'post_type' => 'project'
									);
			wp_update_post( $post_object );

			// Update Project Category Taxonomy Terms
			$project_category_terms = array();
			$gallery_terms = wp_get_object_terms( $portfolio_item->ID, 'portfolio-gallery' );
			foreach ( $gallery_terms as $gallery_term ) {
				if ( isset( $gallery_term->name ) && '' !== $gallery_term->name ) {
					array_push( $project_category_terms, $gallery_term->name );
				} // End If Statement
			} // End For Loop
			if( is_array( $project_category_terms ) && !empty( $project_category_terms ) && !is_wp_error( $project_category_terms ) ) {
				wp_set_object_terms( $portfolio_item->ID, $project_category_terms, 'project-category' );
			} // End If Statement

			// Update Post Meta
			$portfolio_url = get_post_meta( $portfolio_item->ID, '_portfolio_url', true );
			update_post_meta( $portfolio_item->ID, '_url', $portfolio_url );

			// Mark as converted
			$meta_value = 'true';
			update_post_meta( $portfolio_item->ID, '_is_converted_to_project', $meta_value );

			$count_complete++;

		} // End For Loop

		// Check if all are completed
		$transient_test = intval( get_transient( 'portfolio_to_project_count' ) );
		if ( $count_complete == $transient_test ) {
			// Flush the transient
			delete_transient( 'portfolio_to_project_count' );
			return true;
		} else {
			return false;
		} // End If Statement

	} // End convert_portfolio_items_to_projects()

} // End Class
?>