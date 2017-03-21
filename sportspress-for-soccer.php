<?php
/*
 * Plugin Name: SportsPress for Football (Soccer)
 * Plugin URI: http://themeboy.com/
 * Description: A suite of football (soccer) features for SportsPress.
 * Author: ThemeBoy
 * Author URI: http://themeboy.com/
 * Version: 0.9.6
 *
 * Text Domain: sportspress-for-soccer
 * Domain Path: /languages/
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'SportsPress_Soccer' ) ) :

/**
 * Main SportsPress Soccer Class
 *
 * @class SportsPress_Soccer
 * @version	0.9.6
 */
class SportsPress_Soccer {

	/**
	 * Constructor.
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'install' ) );

		// Define constants
		$this->define_constants();
		
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ), 0 );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 30 );
		add_action( 'tgmpa_register', array( $this, 'require_core' ) );

		add_filter( 'gettext', array( $this, 'gettext' ), 20, 3 );
		add_filter( 'sportspress_text', array( $this, 'add_text_options' ) );
		add_filter( 'sportspress_event_box_score_labels', array( $this, 'box_score_labels' ), 10, 3 );
		add_filter( 'sportspress_match_stats_labels', array( $this, 'stats_labels' ) );
		add_filter( 'sportspress_event_performance_players', array( $this, 'players' ), 10, 4 );

		// Define default sport
		add_filter( 'sportspress_default_sport', array( $this, 'default_sport' ) );

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
		update_post_meta( $id, 'sp_singular', 'Own Goal' );
		update_post_meta( $id, 'sp_timed', 1 );
	}

	/**
	 * Define constants.
	*/
	private function define_constants() {
		if ( !defined( 'SP_SOCCER_VERSION' ) )
			define( 'SP_SOCCER_VERSION', '0.9.6' );

		if ( !defined( 'SP_SOCCER_URL' ) )
			define( 'SP_SOCCER_URL', plugin_dir_url( __FILE__ ) );

		if ( !defined( 'SP_SOCCER_DIR' ) )
			define( 'SP_SOCCER_DIR', plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'sportspress-for-soccer' );
		
		// Global + Frontend Locale
		load_textdomain( 'sportspress-for-soccer', WP_LANG_DIR . "/sportspress-for-soccer/sportspress-for-soccer-$locale.mo" );
		load_plugin_textdomain( 'sportspress-for-soccer', false, plugin_basename( dirname( __FILE__ ) . "/languages" ) );
	}

	/**
	 * Enqueue styles.
	 */
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'sportspress-soccer-admin', SP_SOCCER_URL . 'css/admin.css', array( 'sportspress-admin-menu-styles' ), '0.9' );
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
				'version'     => '2.3',
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
					$translated_text = __( 'Matches', 'sportspress-for-soccer' );
					break;
				case 'Event':
					$translated_text = __( 'Match', 'sportspress-for-soccer' );
					break;
				case 'Add New Event':
					$translated_text = __( 'Add New Match', 'sportspress-for-soccer' );
					break;
				case 'Edit Event':
					$translated_text = __( 'Edit Match', 'sportspress-for-soccer' );
					break;
				case 'View Event':
					$translated_text = __( 'View Match', 'sportspress-for-soccer' );
					break;
				case 'View all events':
					$translated_text = __( 'View all matches', 'sportspress-for-soccer' );
					break;
				case 'Venues':
					$translated_text = __( 'Grounds', 'sportspress-for-soccer' );
					break;
				case 'Venue':
					$translated_text = __( 'Ground', 'sportspress-for-soccer' );
					break;
				case 'Edit Venue':
					$translated_text = __( 'Edit Ground', 'sportspress-for-soccer' );
					break;
				case 'Teams':
					$translated_text = __( 'Clubs', 'sportspress-for-soccer' );
					break;
				case 'Team':
					$translated_text = __( 'Club', 'sportspress-for-soccer' );
					break;
				case 'Add New Team':
					$translated_text = __( 'Add New Club', 'sportspress-for-soccer' );
					break;
				case 'Edit Team':
					$translated_text = __( 'Edit Club', 'sportspress-for-soccer' );
					break;
				case 'View Team':
					$translated_text = __( 'View Club', 'sportspress-for-soccer' );
					break;
				case 'View all teams':
					$translated_text = __( 'View all clubs', 'sportspress-for-soccer' );
					break;
			}
		}
		
		return $translated_text;
	}

	/**
	 * Add text options 
	 */
	public function add_text_options( $options = array() ) {
		return array_merge( $options, array(
			__( 'OG', 'sportspress' ),
		) );
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
					$data[ $id ][ $option ] = $goals . ', ' . $owngoals . ' ' . __( 'OG', 'sportspress' );
				} else {
					$data[ $id ][ $option ] = $owngoals . ' ' . __( 'OG', 'sportspress' );
				}
			}
		}

		return $data;
	}

	/**
	 * Define default sport.
	*/
	public function default_sport() {
		return 'soccer';
	}
}

endif;

new SportsPress_Soccer();
