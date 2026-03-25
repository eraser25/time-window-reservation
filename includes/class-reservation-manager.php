<?php
namespace TWRF;

class Reservation_Manager {
	const STATUS_UPCOMING = 'upcoming';
	const STATUS_ACTIVE = 'active';
	const STATUS_CLOSED = 'closed';
	const STATUS_CALCULATING = 'calculating';
	const STATUS_COMPLETED = 'completed';

	public static function get_product_reservation( $product_id ) {
		global $wpdb;
		$table = Database::get_table_name( 'reservations' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE product_id = %d",
				$product_id
			)
		);
	}

	public static function create_reservation( $product_id, $args = array() ) {
		global $wpdb;
		$table = Database::get_table_name( 'reservations' );

		$defaults = array(
			'reservation_start' => current_time( 'mysql' ),
			'reservation_end' => gmdate( 'Y-m-d H:i:s', time() + 600 ),
			'payment_duration' => 300,
			'stock_available' => 1,
			'backup_count' => 5,
			'points_reward' => 10,
			'algorithm_weights' => array(
				'membership_weight' => 0.3,
				'no_recent_win_bonus' => 0.3,
				'join_time_weight' => 0.35,
				'random_factor' => 0.05,
			),
			'cooldown_days' => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$data = array(
			'product_id' => $product_id,
			'reservation_start' => $args['reservation_start'],
			'reservation_end' => $args['reservation_end'],
			'payment_duration' => $args['payment_duration'],
			'stock_available' => $args['stock_available'],
			'backup_count' => $args['backup_count'],
			'points_reward' => $args['points_reward'],
			'algorithm_weights' => json_encode( $args['algorithm_weights'] ),
			'cooldown_days' => $args['cooldown_days'],
			'status' => 'active',
			'created_at' => current_time( 'mysql' ),
			'updated_at' => current_time( 'mysql' ),
		);

		$result = $wpdb->insert( $table, $data );

		if ( $result ) {
			return $wpdb->insert_id;
		}

		return false;
	}

	public static function get_reservation_status( $product_id ) {
		$reservation = self::get_product_reservation( $product_id );

		if ( ! $reservation ) {
			return 'not_reserved';
		}

		$now = current_time( 'mysql' );

		if ( $now < $reservation->reservation_start ) {
			return self::STATUS_UPCOMING;
		}

		if ( $now >= $reservation->reservation_start && $now <= $reservation->reservation_end ) {
			return self::STATUS_ACTIVE;
		}

		if ( $now > $reservation->reservation_end && $reservation->status === 'calculating' ) {
			return self::STATUS_CALCULATING;
		}

		return self::STATUS_COMPLETED;
	}

	public static function join_reservation( $product_id, $user_id ) {
		global $wpdb;

		// Security checks
		if ( ! is_user_logged_in() || get_current_user_id() !== $user_id ) {
			return array( 'success' => false, 'message' => 'Unauthorized' );
		}

		// Rate limiting
		if ( ! Security::check_rate_limit( $user_id ) ) {
			return array( 'success' => false, 'message' => 'Too many attempts. Please try again later.' );
		}

		// Get reservation
		$reservation = self::get_product_reservation( $product_id );
		if ( ! $reservation ) {
			return array( 'success' => false, 'message' => 'Reservation not found' );
		}

		// Check if active
		$status = self::get_reservation_status( $product_id );
		if ( $status !== self::STATUS_ACTIVE ) {
			return array( 'success' => false, 'message' => 'Reservation is not currently active' );
		}

		// Check if already joined
		$sessions_table = Database::get_table_name( 'sessions' );
		$existing = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$sessions_table} WHERE user_id = %d AND reservation_id = %d AND status = %s",
				$user_id,
				$reservation->id,
				'pending'
			)
		);

		if ( $existing ) {
			return array( 'success' => false, 'message' => 'You have already joined this reservation' );
		}

		// Check cooldown
		$cooldown_check = self::check_user_cooldown( $user_id, $reservation->cooldown_days );
		if ( ! $cooldown_check ) {
			return array( 'success' => false, 'message' => 'You must wait before joining another reservation' );
		}

		// Get device fingerprint and IP
		$device_fingerprint = isset( $_POST['device_fingerprint'] ) ? sanitize_text_field( $_POST['device_fingerprint'] ) : '';
		$ip_address = Security::get_user_ip();

		// Check for multi-account abuse
		$abuse_check = Security::check_multi_account_abuse( $ip_address, $device_fingerprint );
		if ( $abuse_check['flagged'] ) {
			// Log but allow with warning
			Security::log_audit(
				$user_id,
				'multi_account_warning',
				'session',
				null,
				null,
				array( 'reason' => $abuse_check['reason'] )
			);
		}

		// Insert session
		$session_data = array(
			'reservation_id' => $reservation->id,
			'user_id' => $user_id,
			'ip_address' => $ip_address,
			'device_fingerprint' => $device_fingerprint,
			'join_timestamp' => current_time( 'mysql' ),
			'status' => 'pending',
			'created_at' => current_time( 'mysql' ),
			'updated_at' => current_time( 'mysql' ),
		);

		$result = $wpdb->insert( $sessions_table, $session_data );

		if ( ! $result ) {
			return array( 'success' => false, 'message' => 'Failed to join reservation' );
		}

		Security::log_audit( $user_id, 'joined_reservation', 'session', $wpdb->insert_id );

		return array(
			'success' => true,
			'message' => 'Successfully joined reservation',
			'session_id' => $wpdb->insert_id,
		);
	}

	public static function get_participant_count( $reservation_id ) {
		global $wpdb;
		$table = Database::get_table_name( 'sessions' );

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE reservation_id = %d AND status = %s",
				$reservation_id,
				'pending'
			)
		);
	}

	public static function check_user_cooldown( $user_id, $cooldown_days ) {
		if ( $cooldown_days <= 0 ) {
			return true;
		}

		global $wpdb;
		$win_table = Database::get_table_name( 'win_sessions' );

		$last_win = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT created_at FROM {$win_table} WHERE user_id = %d AND status = %s ORDER BY created_at DESC LIMIT 1",
				$user_id,
				'winner'
			)
		);

		if ( ! $last_win ) {
			return true;
		}

		$last_win_time = strtotime( $last_win->created_at );
		$cooldown_time = $last_win_time + ( $cooldown_days * DAY_IN_SECONDS );

		return time() > $cooldown_time;
	}

	public static function close_reservation( $reservation_id ) {
		global $wpdb;
		$table = Database::get_table_name( 'reservations' );

		$result = $wpdb->update(
			$table,
			array(
				'status' => self::STATUS_CALCULATING,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $reservation_id )
		);

		// Trigger fair distribution calculation
		if ( $result ) {
			Fair_Distribution::calculate_winners( $reservation_id );
		}

		return $result;
	}
}