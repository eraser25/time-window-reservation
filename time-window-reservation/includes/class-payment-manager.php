<?php
namespace TWRF;

class Payment_Manager {
	public static function get_winner_session( $user_id, $product_id ) {
		global $wpdb;
		$table = Database::get_table_name( 'win_sessions' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE user_id = %d AND product_id = %d AND status = %s
				AND payment_deadline > NOW()
				ORDER BY created_at DESC LIMIT 1",
				$user_id,
				$product_id,
				'winner'
			)
		);
	}

	public static function add_to_cart_and_show_payment_window( $user_id, $product_id ) {
		if ( ! is_user_logged_in() || get_current_user_id() !== $user_id ) {
			return array( 'success' => false, 'message' => 'Unauthorized' );
		}

		$win_session = self::get_winner_session( $user_id, $product_id );
		if ( ! $win_session ) {
			return array( 'success' => false, 'message' => 'You are not eligible to purchase this product' );
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return array( 'success' => false, 'message' => 'Product not found' );
		}

		WC()->cart->add_to_cart( $product_id, 1 );

		Security::log_audit( $user_id, 'added_to_cart', 'cart', $product_id );

		return array(
			'success' => true,
			'message' => 'Product added to cart',
			'cart_url' => wc_get_cart_url(),
			'deadline' => $win_session->payment_deadline,
			'deadline_seconds' => self::get_seconds_until_deadline( $win_session->payment_deadline ),
		);
	}

	public static function handle_payment_expiration( $win_session_id ) {
		global $wpdb;
		$win_table = Database::get_table_name( 'win_sessions' );

		$win_session = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$win_table} WHERE id = %d",
				$win_session_id
			)
		);

		if ( ! $win_session ) {
			return false;
		}

		$wpdb->update(
			$win_table,
			array(
				'status' => 'expired',
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $win_session_id )
		);

		Security::log_audit(
			$win_session->user_id,
			'payment_expired',
			'win_session',
			$win_session_id
		);

		$backup = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$win_table}
				WHERE reservation_id = %d AND status = %s
				ORDER BY win_rank ASC LIMIT 1",
				$win_session->reservation_id,
				'backup'
			)
		);

		if ( $backup ) {
			$reservation = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}twrf_reservations WHERE id = %d",
					$win_session->reservation_id
				)
			);

			$payment_deadline = gmdate( 'Y-m-d H:i:s', time() + $reservation->payment_duration );

			$wpdb->update(
				$win_table,
				array(
					'status' => 'winner',
					'payment_deadline' => $payment_deadline,
					'updated_at' => current_time( 'mysql' ),
					'notes' => 'Promoted from backup due to payment expiration',
				),
				array( 'id' => $backup->id )
			);

			Security::log_audit(
				$backup->user_id,
				'promoted_from_backup',
				'win_session',
				$backup->id,
				'backup',
				'winner'
			);

			self::send_backup_promotion_email( $backup );
		}

		self::send_payment_expired_email( $win_session );

		return true;
	}

	private static function send_backup_promotion_email( $backup ) {
		$user = get_user_by( 'id', $backup->user_id );
		$product = wc_get_product( $backup->product_id );

		if ( ! $user || ! $product ) {
			return;
		}

		$subject = sprintf(
			__( 'Great news! You won a spot for %s', 'twrf' ),
			$product->get_name()
		);

		$message = sprintf(
			__( 'Hi %s, You have been promoted to a winner for the product "%s". You have %d minutes to complete your purchase.', 'twrf' ),
			$user->display_name,
			$product->get_name(),
			round( ( strtotime( $backup->payment_deadline ) - time() ) / 60 )
		);

		wp_mail( $user->user_email, $subject, $message );
	}

	private static function send_payment_expired_email( $win_session ) {
		$user = get_user_by( 'id', $win_session->user_id );
		$product = wc_get_product( $win_session->product_id );

		if ( ! $user || ! $product ) {
			return;
		}

		$subject = sprintf(
			__( 'Payment Expired for %s', 'twrf' ),
			$product->get_name()
		);

		$message = sprintf(
			__( 'Hi %s, Your payment period for "%s" has expired. Unfortunately, your spot has been offered to the next participant.', 'twrf' ),
			$user->display_name,
			$product->get_name()
		);

		wp_mail( $user->user_email, $subject, $message );
	}

	private static function get_seconds_until_deadline( $deadline ) {
		return max( 0, strtotime( $deadline ) - time() );
	}
}