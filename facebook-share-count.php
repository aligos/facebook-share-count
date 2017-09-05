<?php
/**
 * Plugin Name: Facebook Share Count
 * Plugin URI: http://aligos.js.org/wp-plugins/facebook-share-count/
 * Description: Facebook Share Count (FBShareCount) is plugin for check how viral your posts or pages on Facebook.
 * Version: 1.0.0
 * Tested up to: 4.8.1
 * Author: Rahmat Aligos
 * Author URI: http://aligos.js.org
 * License: GPL2
 */

defined( 'ABSPATH' ) or exit( 'Permission Denied' );

if ( ! class_exists( 'FBShareCount' ) ) {
	final class FBShareCount {

		private function __construct() { }

		public function __call( $name = '', $args = array() ) {unset( $name, $args ); return null;}

		public static function instance() {

			static $instance = null;
			if ( null === $instance ) {
				$instance = new FBShareCount;
				$instance->init();
			}
			return $instance;

		}

		public function init() {
			add_shortcode( 'fbscount', array( $this, 'fbscount' ) );

			add_action( 'post_submitbox_misc_actions', array( $this, 'admin_edit_shares' ) );

			add_filter( 'manage_posts_columns', array( $this, 'columns_head' ), 10, 2 );
			add_action( 'manage_posts_custom_column', array( $this, 'columns_content' ), 10, 2 );

			add_filter( 'manage_pages_columns', array( $this, 'columns_head' ), 10, 2 );
			add_action( 'manage_pages_custom_column', array( $this, 'columns_content' ), 10, 2 );
		}

		public function admin_edit_shares() {
			if ( empty( $_GET['post'] ) )
				return;
			?>
			<div class="misc-pub-section curshares misc-pub-curshares">
				<span id="timesshared">
					<img src="https://www.shareicon.net/data/256x256/2015/09/10/98764_facebook_512x512.png" style="height: 1em;vertical-align: middle;"/> <?php _e( 'shares' ); ?>: <b><?php echo $this->fbscount( $_GET['post'] ); ?></b>
				</span>
			</div>
			<?php
		}

		function columns_head( $defaults, $post_type = '' ) {
			if ( ! empty( $post_type ) ) {
				$post_type_object = get_post_type_object( $post_type );
			}

			if ( ! empty( $post_type_object ) ) {
				if ( ! $post_type_object->publicly_queryable ) return;
			}

		    $defaults['fb_shares'] = '<img src="https://www.shareicon.net/data/256x256/2015/09/10/98764_facebook_512x512.png" style="height: 1em;vertical-align: middle;"/> shares';
		    return $defaults;
		}

		function columns_content( $column_name, $post_ID ) {
			
			$post_type = get_post_type( $post_ID );

			if ( ! empty( $post_type ) ) {
				$post_type_object = get_post_type_object( $post_type );
			}

			if ( ! empty( $post_type_object ) ) {
				if ( ! $post_type_object->publicly_queryable ) return;
			}

		    if ( $column_name == 'fb_shares' ) {
		        echo $this->fbscount( $post_ID );
		    }
		}

		public function fbscount( $atts ) {
			$url = get_permalink($atts);
			$resp = wp_remote_get( 'https://fbsharecount.now.sh/?url=' . $url );
		    $code = wp_remote_retrieve_response_code($resp);
		    $body = wp_remote_retrieve_body($resp);
		    if (($code == 200) && !empty($body)) {

		    	$data = json_decode($body, true);
				return '<span style="color: green;font-weight: bold;">' .$data['Count']. '</span>';

			} else {

				return '<span style="color: red;font-weight: bold;">Not Connected.</span>';
			
			}
		}

	}

	function fbsc() {
		return FBsharecount::instance();
	}

	$GLOBALS['fbsc'] = fbsc();
}