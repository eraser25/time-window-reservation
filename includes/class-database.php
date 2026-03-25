<?php
namespace TWRF;

class Database {
	public static function create_tables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = array(

			// Reservations table
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}twrf_reservations (
				id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				product_id BIGINT UNSIGNED NOT NULL,
				reservation_start DATETIME NOT NULL,
				reservation_end DATETIME NOT NULL,
				payment_duration INT NOT NULL DEFAULT 300,
				stock_available INT NOT NULL DEFAULT 1,
				backup_count INT NOT NULL DEFAULT 5,
				points_reward INT NOT NULL DEFAULT 10,
				algorithm_weights JSON,
				cooldown_days INT NOT NULL DEFAULT 0,
				status VARCHAR(20) NOT NULL DEFAULT 'active',
				created_at DATETIME NOT NULL,
				updated_at DATETIME NOT NULL,
				UNIQUE KEY product_reservation (product_id),
				KEY status_index (status),
				KEY date_index (reservation_start, reservation_end),
				INDEX idx_product_id (product_id)
			) {$charset_collate};",

			// Sessions table (participants)
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}twrf_sessions (
				id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				reservation_id BIGINT UNSIGNED NOT NULL,
				user_id BIGINT UNSIGNED NOT NULL,
				ip_address VARCHAR(45) NOT NULL,
				device_fingerprint VARCHAR(255) NOT NULL,
				join_timestamp DATETIME NOT NULL,
				status VARCHAR(20) NOT NULL DEFAULT 'pending',
				created_at DATETIME NOT NULL,
				updated_at DATETIME NOT NULL,
				UNIQUE KEY user_reservation (user_id, reservation_id),
				KEY reservation_index (reservation_id),
				KEY status_index (status),
				KEY user_index (user_id),
				KEY timestamp_index (join_timestamp),
				INDEX idx_ip_device (ip_address, device_fingerprint)
			) {$charset_collate};",

			// Win sessions table
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}twrf_win_sessions (
				id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				reservation_id BIGINT UNSIGNED NOT NULL,
				session_id BIGINT UNSIGNED NOT NULL,
				user_id BIGINT UNSIGNED NOT NULL,
				product_id BIGINT UNSIGNED NOT NULL,
				order_id BIGINT UNSIGNED,
				win_rank INT NOT NULL,
				score FLOAT NOT NULL,
				payment_deadline DATETIME,
				status VARCHAR(20) NOT NULL DEFAULT 'winner',
				notes LONGTEXT,
				created_at DATETIME NOT NULL,
				updated_at DATETIME NOT NULL,
				KEY reservation_index (reservation_id),
				KEY user_index (user_id),
				KEY status_index (status),
				KEY payment_deadline_index (payment_deadline),
				INDEX idx_product_user (product_id, user_id)
			) {$charset_collate};",

			// User points table
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}twrf_user_points (
				id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				user_id BIGINT UNSIGNED NOT NULL,
				points INT NOT NULL DEFAULT 0,
				lifetime_points INT NOT NULL DEFAULT 0,
				last_updated DATETIME NOT NULL,
				created_at DATETIME NOT NULL,
				UNIQUE KEY user_key (user_id),
				KEY points_index (points)
			) {$charset_collate};",

			// Point logs table
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}twrf_point_logs (
				id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				user_id BIGINT UNSIGNED NOT NULL,
				reservation_id BIGINT UNSIGNED,
				transaction_type VARCHAR(20) NOT NULL,
				points INT NOT NULL,
				reason VARCHAR(255),
				expires_at DATETIME,
				created_at DATETIME NOT NULL,
				KEY user_index (user_id),
				KEY reservation_index (reservation_id),
				KEY expires_index (expires_at),
				KEY type_index (transaction_type)
			) {$charset_collate};",

			// Audit logs table
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}twrf_audit_logs (
				id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				user_id BIGINT UNSIGNED,
				action VARCHAR(100) NOT NULL,
				object_type VARCHAR(50),
				object_id BIGINT UNSIGNED,
				old_value LONGTEXT,
				new_value LONGTEXT,
				ip_address VARCHAR(45),
				user_agent TEXT,
				created_at DATETIME NOT NULL,
				KEY action_index (action),
				KEY user_index (user_id),
				KEY date_index (created_at)
			) {$charset_collate};",

		);

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		foreach ( $sql as $query ) {
			dbDelta( $query );
		}

		// Create default options
		add_option( 'twrf_settings', array(
			'enable_recaptcha' => false,
			'recaptcha_site_key' => '',
			'recaptcha_secret_key' => '',
			'rate_limit_attempts' => 5,
			'rate_limit_window' => 60,
			'enable_device_fingerprint' => true,
			'enable_ip_tracking' => true,
		));
	}

	public static function get_table_name( $table ) {
		global $wpdb;
		$tables = array(
			'reservations' => $wpdb->prefix . 'twrf_reservations',
			'sessions' => $wpdb->prefix . 'twrf_sessions',
			'win_sessions' => $wpdb->prefix . 'twrf_win_sessions',
			'user_points' => $wpdb->prefix . 'twrf_user_points',
			'point_logs' => $wpdb->prefix . 'twrf_point_logs',
			'audit_logs' => $wpdb->prefix . 'twrf_audit_logs',
		);

		return isset( $tables[ $table ] ) ? $tables[ $table ] : null;
	}
}
