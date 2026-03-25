<div class="twrf-reservation-button-container" data-product-id="<?php echo esc_attr( $product_id ); ?>">
	<?php if ( 'active' === $status ): ?>
		<?php if ( is_user_logged_in() ): ?>
			<button class="button btn-join-reservation" data-product-id="<?php echo esc_attr( $product_id ); ?>">
				<?php _e( 'Join Reservation', 'twrf' ); ?>
			</button>
		<?php else: ?>
			<p><?php _e( 'Please log in to join the reservation.', 'twrf' ); ?></p>
			<a href="<?php echo esc_url( wp_login_url() ); ?>" class="button"><?php _e( 'Login', 'twrf' ); ?></a>
		<?php endif; ?>
	<?php elseif ( 'upcoming' === $status ): ?>
		<button class="button" disabled><?php _e( 'Reservation Coming Soon', 'twrf' ); ?></button>
	<?php else: ?>
		<button class="button" disabled><?php _e( 'Reservation Closed', 'twrf' ); ?></button>
	<?php endif; ?>
</div>