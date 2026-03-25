<?php
/**
 * Plugin Name: Time Window Reservation & Fair Distribution
 * Plugin URI: https://github.com/eraser25/time-window-reservation
 * Description: Advanced reservation system with fair distribution algorithm for WooCommerce
 * Version: 1.0.0
 * Author: Your Company
 * License: GPL v2 or later
 * Text Domain: twrf
 * Requires Plugins: woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
if ( ! defined( 'TWRF_PLUGIN_FILE' ) ) {
	define( 'TWRF_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'TWRF_PLUGIN_DIR' ) ) {
	define( 'TWRF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'TWRF_PLUGIN_URL' ) ) {
	define( 'TWRF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'TWRF_VERSION' ) ) {
	define( 'TWRF_VERSION', '1.0.0' );
}

// Autoloader
require_once TWRF_PLUGIN_DIR . 'includes/class-autoloader.php';
new TWRF\Autoloader();

// Initialize plugin
add_action( 'plugins_loaded', function() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', function() {
			echo '<div class="error"><p>' . 
				esc_html__( 'Time Window Reservation requires WooCommerce to be installed and activated.', 'twrf' ) . 
			'</p></div>';
		});
		return;
	}

	TWRF\Plugin::instance();
});

// Frontend Shortcodes - DOĞRUDAN KAYIT
add_shortcode( 'twrf_reservation_button', 'twrf_reservation_button_shortcode' );
add_shortcode( 'twrf_countdown', 'twrf_countdown_shortcode' );
add_shortcode( 'twrf_participant_count', 'twrf_participant_count_shortcode' );
add_shortcode( 'twrf_user_points', 'twrf_user_points_shortcode' );

function twrf_reservation_button_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'product_id' => 0,
	), $atts, 'twrf_reservation_button' );

	if ( ! $atts['product_id'] ) {
		return '';
	}

	$product_id = absint( $atts['product_id'] );
	$reservation = TWRF\Reservation_Manager::get_product_reservation( $product_id );

	if ( ! $reservation ) {
		return '';
	}

	$status = TWRF\Reservation_Manager::get_reservation_status( $product_id );

	ob_start();
	?>
	<div class="twrf-reservation-button" data-product-id="<?php echo esc_attr( $product_id ); ?>">
		<?php if ( $status === 'active' ) : ?>
			<?php if ( is_user_logged_in() ) : ?>
				<button class="button button-primary twrf-join-btn" data-product-id="<?php echo esc_attr( $product_id ); ?>">
					<?php esc_html_e( 'Join Reservation', 'twrf' ); ?>
				</button>
			<?php else : ?>
				<p><a href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Login to Join', 'twrf' ); ?></a></p>
			<?php endif; ?>
		<?php elseif ( $status === 'upcoming' ) : ?>
			<p><?php esc_html_e( 'Reservation starts soon...', 'twrf' ); ?></p>
		<?php else : ?>
			<p><?php esc_html_e( 'Reservation has ended.', 'twrf' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

function twrf_countdown_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'product_id' => 0,
	), $atts, 'twrf_countdown' );

	if ( ! $atts['product_id'] ) {
		return '';
	}

	$product_id = absint( $atts['product_id'] );
	$reservation = TWRF\Reservation_Manager::get_product_reservation( $product_id );

	if ( ! $reservation ) {
		return '';
	}

	$status = TWRF\Reservation_Manager::get_reservation_status( $product_id );

	ob_start();
	?>
	<div class="twrf-countdown" data-end-time="<?php echo esc_attr( $reservation->reservation_end ); ?>" data-status="<?php echo esc_attr( $status ); ?>">
		<?php if ( $status === 'active' ) : ?>
			<h3><?php esc_html_e( 'Reservation Ends In:', 'twrf' ); ?></h3>
			<div class="twrf-countdown-display">
				<span class="countdown-value">-- : --</span>
			</div>
		<?php elseif ( $status === 'upcoming' ) : ?>
			<h3><?php esc_html_e( 'Reservation Starts In:', 'twrf' ); ?></h3>
			<div class="twrf-countdown-display">
				<span class="countdown-value">-- : --</span>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

function twrf_participant_count_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'product_id' => 0,
	), $atts, 'twrf_participant_count' );

	if ( ! $atts['product_id'] ) {
		return '';
	}

	$product_id = absint( $atts['product_id'] );
	$reservation = TWRF\Reservation_Manager::get_product_reservation( $product_id );

	if ( ! $reservation ) {
		return '';
	}

	$count = TWRF\Reservation_Manager::get_participant_count( $reservation->id );

	ob_start();
	?>
	<div class="twrf-participant-count">
		<p>
			<?php printf( 
				esc_html__( '%d Participants', 'twrf' ), 
				$count 
			); ?>
		</p>
	</div>
	<?php
	return ob_get_clean();
}

function twrf_user_points_shortcode( $atts ) {
	if ( ! is_user_logged_in() ) {
		return '<p>' . __( 'Please log in to view your points.', 'twrf' ) . '</p>';
	}

	$user_id = get_current_user_id();
	$points = TWRF\Points_System::get_user_points( $user_id );

	ob_start();
	?>
	<div class="twrf-user-points">
		<p><?php printf( __( 'Your Points: %d', 'twrf' ), $points ); ?></p>
	</div>
	<?php
	return ob_get_clean();
}

// Activation hook
register_activation_hook( __FILE__, function() {
	TWRF\Database::create_tables();
	update_option( 'twrf_activated', current_time( 'mysql' ) );
	flush_rewrite_rules();
});

// Deactivation hook
register_deactivation_hook( __FILE__, function() {
	flush_rewrite_rules();
});