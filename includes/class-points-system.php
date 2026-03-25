<?php
namespace TWRF;

class Points_System {
	public static function add_points( $user_id, $points, $reason = '', $reservation_id = null, $expires_at = null ) {
		global $wpdb;

		// Get or create user points record
		$user_points_table = Database::get_table_name( 'user_points' );
		$existing = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$user_points_table} WHERE user_id = %d",
				$user_id
			)
		);

		if ( $existing ) {
			$new_points = $existing->points + $points;
			$new_lifetime = $existing->lifetime_points + $points;

			$wpdb->update(
				$user_points_table,
				array(
					'points' => $new_points,
					'lifetime_points' => $new_lifetime,
					'last_updated' => current_time( 'mysql' ),
				),
				array( 'user_id' => $user_id )
			);
		} else {
			$wpdb->insert(
				$user_points_table,
				array(
					'user_id' => $user_id,
					'points' => $points,
					'lifetime_points' => $points,
					'created_at' => current_time( 'mysql' ),
					'last_updated' => current_time( 'mysql' ),
				)
			);
		}

		// Log the transaction
		$point_logs_table = Database::get_table_name( 'point_logs' );
		$wpdb->insert(
			$point_logs_table,
			array(
				'user_id' => $user_id,
				'reservation_id' => $reservation_id,
				'transaction_type' => 'earned',
				'points' => $points,
				'reason' => $reason,
				'expires_at' => $expires_at,
				'created_at' => current_time( 'mysql' ),
			)
		);

		return true;
	}

	public static function subtract_points( $user_id, $points, $reason = '' ) {
		global $wpdb;

		$user_points_table = Database::get_table_name( 'user_points' );
		$user_points = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$user_points_table} WHERE user_id = %d",
				$user_id
			)
		);

		if ( ! $user_points || $user_points->points < $points ) {
			return false;
		}

		$new_points = max( 0, $user_points->points - $points );

		$wpdb->update(
			$user_points_table,
			array(
				'points' => $new_points,
				'last_updated' => current_time( 'mysql' ),
			),
			array( 'user_id' => $user_id )
		);

		// Log the transaction
		$point_logs_table = Database::get_table_name( 'point_logs' );
		$wpdb->insert(
			$point_logs_table,
			array(
				'user_id' => $user_id,
				'transaction_type' => 'spent',
				'points' => $points,
				'reason' => $reason,
				'created_at' => current_time( 'mysql' ),
			)
		);

		return true;
	}

	public static function get_user_points( $user_id ) {
		global $wpdb;
		$table = Database::get_table_name( 'user_points' );

		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE user_id = %d",
				$user_id
			)
		);

		return $result ? (int) $result->points : 0;
	}

	public static function create_coupon_from_points( $user_id, $points ) {
		if ( ! class_exists( 'WC_Coupon' ) ) {
			return false;
		}

		$coupon = new \WC_Coupon();
		$coupon_code = 'PTS-' . $user_id . '-' . time();

		$coupon->set_code( $coupon_code );
		$coupon->set_discount_type( 'fixed_cart' );
		$coupon->set_amount( $points );
		$coupon->set_individual_use( false );
		$coupon->set_exclude_sale_items( false );
		$coupon->set_usage_limit_per_user( 1 );
		$coupon->set_date_expires( time() + ( 30 * DAY_IN_SECONDS ) );

		$coupon_id = $coupon->save();

		if ( is_wp_error( $coupon_id ) ) {
			return false;
		}

		// Deduct points
		self::subtract_points( $user_id, $points, 'converted_to_coupon' );

		return $coupon_code;
	}

	public static function get_points_history( $user_id, $limit = 50, $offset = 0 ) {
		global $wpdb;
		$table = Database::get_table_name( 'point_logs' );

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE user_id = %d
				ORDER BY created_at DESC
				LIMIT %d OFFSET %d",
				$user_id,
				$limit,
				$offset
			)
		);
	}

	public static function clean_expired_points() {
		global $wpdb;

		$point_logs_table = Database::get_table_name( 'point_logs' );

		// Get expired points
		$expired_logs = $wpdb->get_results(
			"SELECT * FROM {$point_logs_table}
			WHERE expires_at IS NOT NULL AND expires_at < NOW()"
		);

		foreach ( (array) $expired_logs as $log ) {
			if ( 'earned' === $log->transaction_type ) {
				// Deduct expired points from user
				$user_points_table = Database::get_table_name( 'user_points' );
				$user_points = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT * FROM {$user_points_table} WHERE user_id = %d",
						$log->user_id
					)
				);

				if ( $user_points && $user_points->points >= $log->points ) {
					$wpdb->update(
						$user_points_table,
						array(
							'points' => $user_points->points - $log->points,
							'last_updated' => current_time( 'mysql' ),
						),
						array( 'user_id' => $log->user_id )
					);

					// Log the expiration
					$wpdb->insert(
						$point_logs_table,
						array(
							'user_id' => $log->user_id,
							'transaction_type' => 'expired',
							'points' => $log->points,
							'reason' => 'points_expired',
							'created_at' => current_time( 'mysql' ),
						)
					);
				}
			}
		}
	}
}