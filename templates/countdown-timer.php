<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_id = isset( $args['product_id'] ) ? absint( $args['product_id'] ) : 0;

if ( ! $product_id ) {
	return;
}

$reservation = \TWRF\Reservation_Manager::get_product_reservation( $product_id );

if ( ! $reservation ) {
	return;
}

$status = \TWRF\Reservation_Manager::get_reservation_status( $product_id );
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
