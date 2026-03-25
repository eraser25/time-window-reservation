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
