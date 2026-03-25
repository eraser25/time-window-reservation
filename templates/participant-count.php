<div class="twrf-participant-count" data-product-id="<?php echo esc_attr( $product_id ); ?>">
	<p><?php _e( 'Participants: ', 'twrf' ); ?><span class="count"><?php echo Reservation_Manager::get_participant_count( $reservation->id ); ?></span>/<?php echo esc_html( $reservation->stock_available ); ?></p>
</div>