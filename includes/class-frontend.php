<?php
namespace TWRF;

class Frontend {
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_head', array( $this, 'inline_styles' ) );
	}

	public function enqueue_scripts() {
		// Enqueue countdown JS
		wp_enqueue_script(
			'twrf-countdown',
			TWRF_PLUGIN_URL . 'public/js/countdown.js',
			array(),
			TWRF_VERSION,
			true
		);

		// Enqueue reservation JS
		wp_enqueue_script(
			'twrf-reservation',
			TWRF_PLUGIN_URL . 'public/js/reservation.js',
			array( 'jquery' ),
			TWRF_VERSION,
			true
		);

		// Enqueue AJAX handler
		wp_enqueue_script(
			'twrf-ajax',
			TWRF_PLUGIN_URL . 'public/js/ajax-handler.js',
			array( 'jquery' ),
			TWRF_VERSION,
			true
		);

		// Localize script
		wp_localize_script( 'twrf-ajax', 'twrf', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'twrf_nonce' ),
		) );

		// Enqueue frontend CSS
		wp_enqueue_style(
			'twrf-frontend',
			TWRF_PLUGIN_URL . 'public/css/frontend.css',
			array(),
			TWRF_VERSION
		);
	}

	public function inline_styles() {
		?>
		<style>
			.twrf-countdown-display {
				font-size: 24px;
				font-weight: bold;
				color: #d32f2f;
			}
			
			.twrf-join-btn {
				background: #28a745;
				color: white;
				padding: 10px 20px;
				border-radius: 6px;
				cursor: pointer;
			}
		</style>
		<?php
	}
}
