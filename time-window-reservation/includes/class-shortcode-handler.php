<?php
namespace TWRF;

class Shortcode_Handler {
	public function __construct() {
		add_shortcode( 'twrf_reservation_button', array( $this, 'reservation_button' ) );
		add_shortcode( 'twrf_countdown', array( $this, 'countdown_timer' ) );
		add_shortcode( 'twrf_participant_count', array( $this, 'participant_count' ) );
		add_shortcode( 'twrf_user_points', array( $this, 'user_points' ) );
	}

	public function reservation_button( $atts ) {
		$atts = shortcode_atts( array(
			'product_id' => 0,
		), $atts, 'twrf_reservation_button' );

		if ( ! $atts['product_id'] ) {
			return '';
		}

		$product_id = absint( $atts['product_id'] );
		$reservation = Reservation_Manager::get_product_reservation( $product_id );

		if ( ! $reservation ) {
			return '';
		}

		$status = Reservation_Manager::get_reservation_status( $product_id );

		ob_start();
		include TWRF_PLUGIN_DIR . 'templates/reservation-button.php';
		return ob_get_clean();
	}

	public function countdown_timer( $atts ) {
		$atts = shortcode_atts( array(
			'product_id' => 0,
		), $atts, 'twrf_countdown' );

		if ( ! $atts['product_id'] ) {
			return '';
		}

		$product_id = absint( $atts['product_id'] );
		$reservation = Reservation_Manager::get_product_reservation( $product_id );

		if ( ! $reservation ) {
			return '';
		}

		$status = Reservation_Manager::get_reservation_status( $product_id );

		ob_start();
		include TWRF_PLUGIN_DIR . 'templates/countdown-timer.php';
		return ob_get_clean();
	}

	public function participant_count( $atts ) {
		$atts = shortcode_atts( array(
			'product_id' => 0,
		), $atts, 'twrf_participant_count' );

		if ( ! $atts['product_id'] ) {
			return '';
		}

		$product_id = absint( $atts['product_id'] );
		$reservation = Reservation_Manager::get_product_reservation( $product_id );

		if ( ! $reservation ) {
			return '';
		}

		$count = Reservation_Manager::get_participant_count( $reservation->id );

		ob_start();
		include TWRF_PLUGIN_DIR . 'templates/participant-count.php';
		return ob_get_clean();
	}

	public function user_points( $atts ) {
		if ( ! is_user_logged_in() ) {
			return '<p>' . __( 'Please log in to view your points.', 'twrf' ) . '</p>';
		}

		$user_id = get_current_user_id();
		$points = Points_System::get_user_points( $user_id );

		ob_start();
		?>
		<div class="twrf-user-points">
			<p><?php printf( __( 'Your Points: %d', 'twrf' ), $points ); ?></p>
		</div>
		<?php
		return ob_get_clean();
	}
}