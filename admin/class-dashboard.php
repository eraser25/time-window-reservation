<?php
namespace TWRF;

class Dashboard {
	public static function render() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'Unauthorized' );
		}

		global $wpdb;

		// Get statistics
		$total_reservations = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}twrf_reservations" );
		$total_participants = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}twrf_sessions" );
		$total_winners = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}twrf_win_sessions WHERE status = 'winner'" );
		$total_backups = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}twrf_win_sessions WHERE status = 'backup'" );
		$total_points_issued = (int) $wpdb->get_var( "SELECT SUM(points) FROM {$wpdb->prefix}twrf_point_logs WHERE transaction_type = 'earned'" ) ?? 0;

		// Get recent reservations
		$reservations = $wpdb->get_results(
			"SELECT r.*, p.post_title as product_name, COUNT(s.id) as participant_count
			FROM {$wpdb->prefix}twrf_reservations r
			LEFT JOIN {$wpdb->posts} p ON r.product_id = p.ID
			LEFT JOIN {$wpdb->prefix}twrf_sessions s ON r.id = s.reservation_id
			GROUP BY r.id
			ORDER BY r.created_at DESC LIMIT 10"
		);

		// Get active reservations
		$active = $wpdb->get_results(
			"SELECT r.*, p.post_title as product_name
			FROM {$wpdb->prefix}twrf_reservations r
			LEFT JOIN {$wpdb->posts} p ON r.product_id = p.ID
			WHERE r.status = 'active' AND r.reservation_end > NOW()"
		);

		include TWRF_PLUGIN_DIR . 'templates/admin-dashboard.php';
	}

	public static function get_reservation_participants( $reservation_id ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT s.*, u.display_name, u.user_email, w.status as win_status, w.score, w.win_rank
				FROM {$wpdb->prefix}twrf_sessions s
				LEFT JOIN {$wpdb->users} u ON s.user_id = u.ID
				LEFT JOIN {$wpdb->prefix}twrf_win_sessions w ON s.id = w.session_id
				WHERE s.reservation_id = %d
				ORDER BY s.join_timestamp ASC",
				$reservation_id
			)
		);
	}

	public static function get_winner_stats( $reservation_id ) {
		global $wpdb;

		return array(
			'winners' => (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}twrf_win_sessions
					WHERE reservation_id = %d AND status = 'winner'",
					$reservation_id
				)
			),
			'backups' => (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}twrf_win_sessions
					WHERE reservation_id = %d AND status = 'backup'",
					$reservation_id
				)
			),
			'expired' => (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}twrf_win_sessions
					WHERE reservation_id = %d AND status = 'expired'",
					$reservation_id
				)
			),
		);
	}

	public static function get_top_participants( $limit = 5 ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT u.ID, u.display_name, u.user_email, COUNT(w.id) as win_count, SUM(w.score) as total_score
				FROM {$wpdb->users} u
				LEFT JOIN {$wpdb->prefix}twrf_win_sessions w ON u.ID = w.user_id AND w.status = 'winner'
				GROUP BY u.ID
				ORDER BY win_count DESC, total_score DESC
				LIMIT %d",
				$limit
			)
		);
	}

	public static function get_point_distribution() {
		global $wpdb;

		return $wpdb->get_results(
			"SELECT DATE(created_at) as date, SUM(CASE WHEN transaction_type = 'earned' THEN points ELSE -points END) as net_points
			FROM {$wpdb->prefix}twrf_point_logs
			WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
			GROUP BY DATE(created_at)
			ORDER BY date ASC"
		);
	}
}