<?php
/*
Plugin Name: SportsPress for Football (Soccer)
Plugin URI: http://themeboy.com/
Description: A suite of football (soccer) features for SportsPress.
Author: ThemeBoy
Author URI: http://themeboy.com/
Version: 0.9.2
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'SportsPress_Soccer' ) ) :

/**
 * Main SportsPress Soccer Class
 *
 * @class SportsPress_Soccer
 * @version	0.9.2
 */
class SportsPress_Soccer {

	/**
	 * Constructor.
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'install' ) );

		// Define constants
		$this->define_constants();

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 30 );
		add_action( 'tgmpa_register', array( $this, 'require_core' ) );

		add_filter( 'gettext', array( $this, 'gettext' ), 20, 3 );
		add_filter( 'sportspress_event_box_score_labels', array( $this, 'box_score_labels' ), 10, 3 );
		add_filter( 'sportspress_match_stats_labels', array( $this, 'stats_labels' ) );
		add_filter( 'sportspress_event_performance_players', array( $this, 'players' ), 10, 4 );

		// Include required files
		$this->includes();
	}

	/**
	 * Install.
	*/
	public static function install() {
		if ( get_page_by_path( 'owngoals', OBJECT, 'sp_performance' ) ) return;

		$post = array(
			'post_title' => 'Own Goals',
			'post_name' => 'owngoals',
			'post_type' => 'sp_performance',
			'post_excerpt' => 'Own goals',
			'menu_order' => 200,
			'post_status' => 'publish',
		);

		$id = wp_insert_post( $post );

		update_post_meta( $id, 'sp_icon', 'soccerball' );
		update_post_meta( $id, 'sp_color', '#d4000f' );
	}

	/**
	 * Define constants.
	*/
	private function define_constants() {
		if ( !defined( 'SP_FOOTBALL_VERSION' ) )
			define( 'SP_FOOTBALL_VERSION', '0.9.2' );

		if ( !defined( 'SP_FOOTBALL_URL' ) )
			define( 'SP_FOOTBALL_URL', plugin_dir_url( __FILE__ ) );

		if ( !defined( 'SP_FOOTBALL_DIR' ) )
			define( 'SP_FOOTBALL_DIR', plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Enqueue styles.
	 */
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'sportspress-soccer-admin', SP_FOOTBALL_URL . 'css/admin.css', array( 'sportspress-admin-menu-styles' ), '0.9' );
	}

	/**
	 * Include required files.
	*/
	private function includes() {
		require_once dirname( __FILE__ ) . '/includes/class-tgm-plugin-activation.php';
	}

	/**
	 * Require SportsPress core.
	*/
	public static function require_core() {
		$plugins = array(
			array(
				'name'        => 'SportsPress',
				'slug'        => 'sportspress',
				'required'    => true,
				'is_callable' => array( 'SportsPress', 'instance' ),
			),
		);

		$config = array(
			'default_path' => '',
			'menu'         => 'tgmpa-install-plugins',
			'has_notices'  => true,
			'dismissable'  => true,
			'is_automatic' => true,
			'message'      => '',
			'strings'      => array(
				'nag_type' => 'updated'
			)
		);

		tgmpa( $plugins, $config );
	}

	/** 
	 * Text filter.
	 */
	public function gettext( $translated_text, $untranslated_text, $domain ) {
		if ( $domain == 'sportspress' ) {
			switch ( $untranslated_text ) {
				case 'Events':
					$translated_text = __( 'Matches', 'sportspress' );
					break;
				case 'Event':
					$translated_text = __( 'Match', 'sportspress' );
					break;
				case 'Add New Event':
					$translated_text = __( 'Add New Match', 'sportspress' );
					break;
				case 'Edit Event':
					$translated_text = __( 'Edit Match', 'sportspress' );
					break;
				case 'View Event':
					$translated_text = __( 'View Match', 'sportspress' );
					break;
				case 'View all events':
					$translated_text = __( 'View all matches', 'sportspress' );
					break;
				case 'Venues':
					$translated_text = __( 'Fields', 'sportspress' );
					break;
				case 'Venue':
					$translated_text = __( 'Field', 'sportspress' );
					break;
				case 'Edit Venue':
					$translated_text = __( 'Edit Field', 'sportspress' );
					break;
				case 'Teams':
					$translated_text = __( 'Clubs', 'sportspress' );
					break;
				case 'Team':
					$translated_text = __( 'Club', 'sportspress' );
					break;
				case 'Add New Team':
					$translated_text = __( 'Add New Club', 'sportspress' );
					break;
				case 'Edit Team':
					$translated_text = __( 'Edit Club', 'sportspress' );
					break;
				case 'View Team':
					$translated_text = __( 'View Club', 'sportspress' );
					break;
				case 'View all teams':
					$translated_text = __( 'View all clubs', 'sportspress' );
					break;
			}
		}
		
		return $translated_text;
	}

	/**
	 * Hide own goals from box score.
	*/
	public function box_score_labels( $labels = array(), $event = null, $mode = 'values' ) {
		if ( 'values' == $mode ) {
			unset( $labels['owngoals'] );
		}
		return $labels;
	}

	/**
	 * Hide own goals from match stats.
	*/
	public function stats_labels( $labels = array() ) {
		unset( $labels['owngoals'] );
		return $labels;
	}

	/**
	 * Append own goals to box score.
	*/
	public function players( $data = array(), $lineups = array(), $subs = array(), $mode = 'values' ) {
		if ( 'icons' == $mode ) return $data;

		foreach ( $data as $id => $performance ) {
			$owngoals = sp_array_value( $performance, 'owngoals', 0 );
			if ( $owngoals ) {
				$option = sp_get_main_performance_option();
				$goals = sp_array_value( $performance, $option, 0 );
				if ( $goals ) {
					$data[ $id ][ 'goals' ] = $goals . ', ' . $owngoals . ' ' . get_option( 'sportspress_own_goals_notation', 'OG' );
				} else {
					$data[ $id ][ 'goals' ] = $owngoals . ' ' . get_option( 'sportspress_own_goals_notation', 'OG' );
				}
			}
		}

		return $data;
	}
}

endif;

new SportsPress_Soccer();
