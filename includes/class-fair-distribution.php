<?php
namespace TWRF;

class Fair_Distribution {

	public static function calculate_winners( $reservation_id ) {
		global $wpdb;

		$reservation = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}twrf_reservations WHERE id = %d",
				$reservation_id
			)
		);

		if ( ! $reservation ) {
			return false;
		}

		// Get all participants
		$participants = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT s.*, u.user_registered FROM {$wpdb->prefix}twrf_sessions s
				LEFT JOIN {$wpdb->users} u ON s.user_id = u.ID
				WHERE s.reservation_id = %d AND s.status = %s
				ORDER BY s.join_timestamp ASC",
				$reservation_id,
				'pending'
			)
		);

		if ( empty( $participants ) ) {
			return false;
		}

		$stock = (int) $reservation->stock_available;
		$backup_count = (int) $reservation->backup_count;
		$weights = json_decode( $reservation->algorithm_weights, true );

		// Calculate scores
		$scored_participants = array();
		foreach ( $participants as $participant ) {
			$score = self::calculate_participant_score( $participant, $reservation, $weights );
			$scored_participants[] = array(
				'participant' => $participant,
				'score' => $score,
			);
		}

		// Sort by score descending
		usort( $scored_participants, function( $a, $b ) {
			return $b['score'] <=> $a['score'];
		});

		// Assign winners and backups
		$winners = array();
		$backups = array();
		$non_winners = array();

		foreach ( $scored_participants as $index => $data ) {
			if ( $index < $stock ) {
				$winners[] = $data;
			} elseif ( $index < $stock + $backup_count ) {
				$backups[] = $data;
			} else {
				$non_winners[] = $data;
			}
		}

		// Save results
		self::save_win_sessions( $reservation, $winners, $backups, $non_winners );

		// Award points to non-winners
		self::award_non_winner_points( $reservation, $non_winners );

		// Update reservation status
		$wpdb->update(
			$wpdb->prefix . 'twrf_reservations',
			array(
				'status' => 'completed',
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $reservation_id )
		);

		return true;
	}

	private static function calculate_participant_score( $participant, $reservation, $weights ) {
		$score = 0;

		// 1. Membership duration weight
		if ( ! empty( $participant->user_registered ) ) {
			$membership_days = ( current_time( 'timestamp' ) - strtotime( $participant->user_registered ) ) / DAY_IN_SECONDS;
			$membership_score = min( $membership_days / 365, 1.0 ); // Normalize to max 1.0 (1 year)
			$score += $membership_score * $weights['membership_weight'];
		}

		// 2. No recent win bonus
		$no_recent_win_bonus = self::calculate_no_recent_win_bonus( $participant->user_id );
		$score += $no_recent_win_bonus * $weights['no_recent_win_bonus'];

		// 3. Join time weight (earlier join = better score)
		$join_time_score = self::calculate_join_time_score( $participant->join_timestamp );
		$score += $join_time_score * $weights['join_time_weight'];

		// 4. Random factor (0-5%)
		$random_factor = ( rand( 0, 10000 ) / 10000 ) * $weights['random_factor'];
		$score += $random_factor;

		return round( $score, 6 );
	}

	private static function calculate_no_recent_win_bonus( $user_id ) {
		global $wpdb;

		// Check if user won in last 30 days
		$recent_win = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT created_at FROM {$wpdb->prefix}twrf_win_sessions
				WHERE user_id = %d AND status = %s AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
				ORDER BY created_at DESC LIMIT 1",
				$user_id,
				'winner'
			)
		);

		if ( ! $recent_win ) {
			return 1.0; // Full bonus
		}

		// Partial bonus based on time since last win
		$days_since_win = ( current_time( 'timestamp' ) - strtotime( $recent_win->created_at ) ) / DAY_IN_SECONDS;
		return min( $days_since_win / 30, 0.5 ); // Max 0.5 bonus
	}

	private static function calculate_join_time_score( $join_timestamp ) {
		global $wpdb;

		// Get earliest and latest join times for this batch
		$times = $wpdb->get_row(
			"SELECT MIN(join_timestamp) as earliest, MAX(join_timestamp) as latest
			FROM {$wpdb->prefix}twrf_sessions
			WHERE status = %s
			ORDER BY join_timestamp DESC LIMIT 1",
			null,
			'pending'
		);

		if ( ! $times || $times->earliest === $times->latest ) {
			return 1.0; // Everyone joined at same time
		}

		$join_time_ms = strtotime( $join_timestamp );
		$earliest_ms = strtotime( $times->earliest );
		$latest_ms = strtotime( $times->latest );

		$time_range = $latest_ms - $earliest_ms;
		if ( $time_range === 0 ) {
			return 1.0;
		}

		$user_position = $join_time_ms - $earliest_ms;
		return 1.0 - ( $user_position / $time_range ); // Earlier = higher score
	}

	private static function save_win_sessions( $reservation, $winners, $backups, $non_winners ) {
		global $wpdb;
		$win_table = Database::get_table_name( 'win_sessions' );

		// Process winners
		foreach ( $winners as $index => $data ) {
			$payment_deadline = gmdate( 'Y-m-d H:i:s', time() + $reservation->payment_duration );

			$wpdb->insert(
				$win_table,
				array(
					'reservation_id' => $reservation->id,
					'session_id' => $data['participant']->id,
					'user_id' => $data['participant']->user_id,
					'product_id' => $reservation->product_id,
					'win_rank' => $index + 1,
					'score' => $data['score'],
					'payment_deadline' => $payment_deadline,
					'status' => 'winner',
					'created_at' => current_time( 'mysql' ),
					'updated_at' => current_time( 'mysql' ),
				)
			);

			Security::log_audit(
				$data['participant']->user_id,
				'reservation_won',
				'win_session',
				$wpdb->insert_id,
				null,
				array( 'rank' => $index + 1, 'score' => $data['score'] )
			);
		}

		// Process backups
		foreach ( $backups as $index => $data ) {
			$wpdb->insert(
				$win_table,
				array(
					'reservation_id' => $reservation->id,
					'session_id' => $data['participant']->id,
					'user_id' => $data['participant']->user_id,
					'product_id' => $reservation->product_id,
					'win_rank' => count( $winners ) + $index + 1,
					'score' => $data['score'],
					'status' => 'backup',
					'created_at' => current_time( 'mysql' ),
					'updated_at' => current_time( 'mysql' ),
				)
			);

			Security::log_audit(
				$data['participant']->user_id,
				'reservation_backup',
				'win_session',
				$wpdb->insert_id,
				null,
				array( 'rank' => count( $winners ) + $index + 1, 'score' => $data['score'] )
			);
		}
	}

	private static function award_non_winner_points( $reservation, $non_winners ) {
		$points_reward = (int) $reservation->points_reward;

		foreach ( $non_winners as $data ) {
			Points_System::add_points(
				$data['participant']->user_id,
				$points_reward,
				'non_winner_reward',
				$reservation->id,
				null // no expiration
			);

			Security::log_audit(
				$data['participant']->user_id,
				'points_awarded',
				'user_points',
				null,
				null,
				array( 'points' => $points_reward, 'reason' => 'non_winner_reward' )
			);
		}
	}
}