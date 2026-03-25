<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Extract shortcode attributes
$product_id = isset( $args['product_id'] ) ? absint( $args['product_id'] ) : 0;

if ( ! $product_id ) {
	return;
}

// Get reservation
$reservation = \TWRF\Reservation_Manager::get_product_reservation( $product_id );

if ( ! $reservation ) {
	return;
}

// Get participant count
$count = \TWRF\Reservation_Manager::get_participant_count( $reservation->id );
?>

<div class="twrf-participant-count">
	<p class="twrf-participants-label">
		<?php printf( 
			esc_html__( '%d Participants', 'twrf' ), 
			$count 
		); ?>
	</p>
</div>
