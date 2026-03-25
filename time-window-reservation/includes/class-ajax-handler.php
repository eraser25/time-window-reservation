<?php
namespace TWRF;

class AJAX_Handler {
	public function __construct() {
		add_action( 'wp_ajax_twrf_get_reservation_status', array( $this, 'get_reservation_status' ) );
		add_action( 'wp_ajax_twrf_get_participant_count', array( $this, 'get_participant_count' ) );
		add_action( 'wp_ajax_twrf_join_reservation', array( $this, 'join_reservation' ) );
		add_action( 'wp_ajax_twrf_get_time_until_deadline', array( $this, 'get_time_until_deadline' ) );
		add_action( 'wp_ajax_twrf_add_to_cart_payment', array( $this, 'add_to_cart_payment' ) );
		add_action( 'wp_ajax_twrf_get_user_points', array( $this, 'get_user_points' ) );

		add_action( 'wp_ajax_twrf_admin_get_participants', array( $this, 'admin_get_participants' ) );
		add_action( 'wp_ajax_twrf_admin_override_winner', array( $this, 'admin_override_winner' ) );
	}

	public function get_reservation_status() {
		check_ajax_referer( 'twrf_nonce' );

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		if ( ! $product_id ) {
			wp_send_json_error( array( 'message' => 'Invalid product ID' ) );
		}

		$status = Reservation_Manager::get_reservation_status( $product_id );
		$reservation = Reservation_Manager::get_product_reservation( $product_id );

		wp_send_json_success( array(
			'status' => $status,
			'reservation_start' => $reservation ? $reservation->reservation_start : null,
			'reservation_end' => $reservation ? $reservation->reservation_end : null,
		));
	}

	public function get_participant_count() {
		check_ajax_referer( 'twrf_nonce' );

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$reservation = Reservation_Manager::get_product_reservation( $product_id );

		if ( ! $reservation ) {
			wp_send_json_error( array( 'message' => 'Reservation not found' ) );
		}

		$count = Reservation_Manager::get_participant_count( $reservation->id );

		wp_send_json_success( array(
			'count' => $count,
			'stock' => $reservation->stock_available,
		));
	}

	public function join_reservation() {
		check_ajax_referer( 'twrf_nonce' );

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$user_id = get_current_user_id();

		if ( ! $user_id || ! $product_id ) {
			wp_send_json_error( array( 'message' => 'Invalid request' ) );
		}

		$result = Reservation_Manager::join_reservation( $product_id, $user_id );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	public function get_time_until_deadline() {
		check_ajax_referer( 'twrf_nonce' );

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$user_id = get_current_user_id();

		if ( ! $user_id || ! $product_id ) {
			wp_send_json_error();
		}

		$win_session = Payment_Manager::get_winner_session( $user_id, $product_id );

		if ( ! $win_session ) {
			wp_send_json_error( array( 'message' => 'Not a winner' ) );
		}

		$seconds = max( 0, strtotime( $win_session->payment_deadline ) - time() );

		wp_send_json_success( array(
			'seconds' => $seconds,
			'deadline' => $win_session->payment_deadline,
		));
	}

	public function add_to_cart_payment() {
		check_ajax_referer( 'twrf_nonce' );

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$user_id = get_current_user_id();

		if ( ! $user_id || ! $product_id ) {
			wp_send_json_error();
		}

		$result = Payment_Manager::add_to_cart_and_show_payment_window( $user_id, $product_id );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	public function get_user_points() {
		check_ajax_referer( 'twrf_nonce' );

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error();
		}

		$points = Points_System::get_user_points( $user_id );

		wp_send_json_success( array( 'points' => $points ) );
	}

	public function admin_get_participants() {
		check_ajax_referer( 'twrf_admin_nonce' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		$reservation_id = isset( $_POST['reservation_id'] ) ? absint( $_POST['reservation_id'] ) : 0;

		$participants = Dashboard::get_reservation_participants( $reservation_id );

		wp_send_json_success( array( 'participants' => $participants ) );
	}

	public function admin_override_winner() {
		check_ajax_referer( 'twrf_admin_nonce' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		$user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$action = isset( $_POST['action_type'] ) ? sanitize_text_field( $_POST['action_type'] ) : '';

		if ( ! $user_id || ! $product_id ) {
			wp_send_json_error( array( 'message' => 'Invalid parameters' ) );
		}

		Security::log_audit(
			get_current_user_id(),
			'manual_override_' . $action,
			'user',
			$user_id,
			null,
			array( 'product_id' => $product_id, 'action' => $action )
		);

		wp_send_json_success( array( 'message' => 'Override applied' ) );
	}
}