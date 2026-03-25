<div class="twrf-countdown" data-product-id="<?php echo esc_attr( $product_id ); ?>" data-start="<?php echo esc_attr( $reservation->reservation_start ); ?>" data-end="<?php echo esc_attr( $reservation->reservation_end ); ?>">
	<h3><?php _e( 'Reservation Countdown', 'twrf' ); ?></h3>
	<div class="countdown-display">
		<span class="countdown-text"></span>
	</div>
</div>