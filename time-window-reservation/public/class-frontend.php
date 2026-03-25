<?php
namespace TWRF;

class Frontend {
	public function __construct() {
		add_filter( 'woocommerce_product_single_template', array( $this, 'include_reservation_template' ) );
	}

	public function include_reservation_template( $template ) {
		global $product;

		if ( ! $product ) {
			return $template;
		}

		$reservation = Reservation_Manager::get_product_reservation( $product->get_id() );

		if ( $reservation ) {
			// Product has a reservation, we can add UI elements
			add_action( 'woocommerce_after_add_to_cart_button', function() {
				global $product;
				$reservation = Reservation_Manager::get_product_reservation( $product->get_id() );
				if ( $reservation ) {
					echo do_shortcode( '[twrf_countdown product_id="' . $product->get_id() . '"]' );
					echo do_shortcode( '[twrf_participant_count product_id="' . $product->get_id() . '"]' );
					echo do_shortcode( '[twrf_reservation_button product_id="' . $product->get_id() . '"]' );
				}
			});
		}

		return $template;
	}
}