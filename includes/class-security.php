<?php
namespace TWRF;

class Security {
	const RATE_LIMIT_OPTION = 'twrf_rate_limit_';

	public static function get_user_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'] ?? '';
		}

		return sanitize_text_field( $ip );
	}

	public static function check_rate_limit( $user_id ) {
		$settings = get_option( 'twrf_settings', array() );
		$attempts = (int) $settings['rate_limit_attempts'] ?? 5;
		$window = (int) $settings['rate_limit_window'] ?? 60;

		$option_key = self::RATE_LIMIT_OPTION . $user_id;
		$attempt_data = get_transient( $option_key );

		if ( false === $attempt_data ) {
			$attempt_data = array( 'count' => 1, 'first_attempt' => time() );
		} else {
			$attempt_data['count']++;
		}

		set_transient( $option_key, $attempt_data, $window );

		return $attempt_data['count'] <= $attempts;
	}

	public static function verify_recaptcha( $token ) {
		$settings = get_option( 'twrf_settings', array() );

		if ( empty( $settings['enable_recaptcha'] ) || empty( $settings['recaptcha_secret_key'] ) ) {
			return true; // reCAPTCHA disabled
		}

		$response = wp_remote_post(
			'https://www.google.com/recaptcha/api/siteverify',
			array(
				'body' => array(
					'secret' => $settings['recaptcha_secret_key'],
					'response' => $token,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return isset( $body['success'] ) && $body['success'] && $body['score'] > 0.5;
	}

	public static function check_multi_account_abuse( $ip_address, $device_fingerprint ) {
		global $wpdb;
		$sessions_table = Database::get_table_name( 'sessions' );

		// Check IP
		$ip_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT user_id) FROM {$sessions_table}
				WHERE ip_address = %s AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)",
				$ip_address
			)
		);

		if ( $ip_count > 5 ) {
			return array(
				'flagged' => true,
				'reason' => sprintf( 'IP %s has %d participants in 24h', $ip_address, $ip_count ),
			);
		}

		// Check device fingerprint
		if ( ! empty( $device_fingerprint ) ) {
			$device_count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(DISTINCT user_id) FROM {$sessions_table}
					WHERE device_fingerprint = %s AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)",
					$device_fingerprint
				)
			);

			if ( $device_count > 5 ) {
				return array(
					'flagged' => true,
					'reason' => sprintf( 'Device %s has %d participants in 24h', $device_fingerprint, $device_count ),
				);
			}
		}

		return array( 'flagged' => false );
	}

	public static function log_audit( $user_id, $action, $object_type = null, $object_id = null, $old_value = null, $new_value = null ) {
		global $wpdb;
		$table = Database::get_table_name( 'audit_logs' );

		$wpdb->insert(
			$table,
			array(
				'user_id' => $user_id,
				'action' => $action,
				'object_type' => $object_type,
				'object_id' => $object_id,
				'old_value' => is_array( $old_value ) ? json_encode( $old_value ) : $old_value,
				'new_value' => is_array( $new_value ) ? json_encode( $new_value ) : $new_value,
				'ip_address' => self::get_user_ip(),
				'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
				'created_at' => current_time( 'mysql' ),
			)
		);
	}

	public static function sanitize_input( $input, $type = 'text' ) {
		switch ( $type ) {
			case 'email':
				return sanitize_email( $input );
			case 'url':
				return esc_url( $input );
			case 'int':
				return absint( $input );
			case 'json':
				return json_decode( stripslashes( $input ), true );
			default:
				return sanitize_text_field( $input );
		}
	}
}