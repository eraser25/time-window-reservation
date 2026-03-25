<?php
namespace TWRF;

class Plugin {
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		// Load constants
		if ( ! defined( 'TWRF_PLUGIN_FILE' ) ) {
			define( 'TWRF_PLUGIN_FILE', plugin_dir_path( dirname( __FILE__ ) ) . 'time-window-reservation.php' );
		}
		if ( ! defined( 'TWRF_PLUGIN_DIR' ) ) {
			define( 'TWRF_PLUGIN_DIR', plugin_dir_path( dirname( __FILE__ ) ) );
		}
		if ( ! defined( 'TWRF_PLUGIN_URL' ) ) {
			define( 'TWRF_PLUGIN_URL', plugin_dir_url( dirname( __FILE__ ) ) );
		}
		if ( ! defined( 'TWRF_VERSION' ) ) {
			define( 'TWRF_VERSION', '1.0.0' );
		}

		// Initialize core components
		new Database();
		new Admin();
		new Frontend();
		new AJAX_Handler();
		new Shortcode_Handler();

		// Hooks
		add_action( 'init', array( $this, 'register_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

		// Background jobs
		add_action( 'twrf_hourly_check', array( $this, 'process_expired_reservations' ) );
		if ( ! wp_next_scheduled( 'twrf_hourly_check' ) ) {
			wp_schedule_event( time(), 'hourly', 'twrf_hourly_check' );
		}
	}

	public function register_assets() {
		wp_register_script(
			'twrf-device-fingerprint',
			TWRF_PLUGIN_URL . 'assets/js/device-fingerprint.js',
			array(),
			TWRF_VERSION,
			true
		);
	}

	public function enqueue_frontend_assets() {
		// Handled by Frontend class
	}

	public function process_expired_reservations() {
		global $wpdb;
		$table = $wpdb->prefix . 'twrf_win_sessions';

		$expired = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE status = %s AND payment_deadline < %s",
				'pending_payment',
				current_time( 'mysql' )
			)
		);

		foreach ( (array) $expired as $session ) {
			Payment_Manager::handle_payment_expiration( $session->id );
		}
	}
}