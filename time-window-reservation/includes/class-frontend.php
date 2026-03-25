<?php
namespace TWRF;

class Frontend {
	public function __construct() {
		add_filter( 'woocommerce_product_single_template', array( $this, 'include_reservation_template' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	public function include_reservation_template( $template ) {
		global $product;

		if ( ! $product ) {
			return $template;
		}

		$reservation = Reservation_Manager::get_product_reservation( $product->get_id() );

		if ( $reservation ) {
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

	public function enqueue_frontend_assets() {
		if ( is_product() ) {
			wp_enqueue_style(
				'twrf-frontend',
				TWRF_PLUGIN_URL . 'public/css/frontend.css',
				array(),
				TWRF_VERSION
			);

			wp_enqueue_script(
				'twrf-device-fingerprint',
				TWRF_PLUGIN_URL . 'assets/js/device-fingerprint.js',
				array(),
				TWRF_VERSION,
				true
			);

			wp_enqueue_script(
				'twrf-countdown',
				TWRF_PLUGIN_URL . 'public/js/countdown.js',
				array( 'jquery' ),
				TWRF_VERSION,
				true
			);

			wp_enqueue_script(
				'twrf-reservation',
				TWRF_PLUGIN_URL . 'public/js/reservation.js',
				array( 'jquery', 'twrf-device-fingerprint', 'twrf-countdown' ),
				TWRF_VERSION,
				true
			);

			wp_enqueue_script(
				'twrf-ajax-handler',
				TWRF_PLUGIN_URL . 'public/js/ajax-handler.js',
				array( 'jquery' ),
				TWRF_VERSION,
				true
			);

			wp_localize_script(
				'twrf-reservation',
				'twrf_data',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'twrf_nonce' ),
				)
			);
		}
	}
}