<?php
namespace TWRF;

class Settings_Page {
	public static function render() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'Unauthorized' );
		}

		$settings = get_option( 'twrf_settings', array() );

		// Handle form submission
		if ( isset( $_POST['submit'] ) && wp_verify_nonce( $_POST['twrf_settings_nonce'], 'twrf_settings_action' ) ) {
			$settings = array(
				'enable_recaptcha' => isset( $_POST['enable_recaptcha'] ),
				'recaptcha_site_key' => sanitize_text_field( $_POST['recaptcha_site_key'] ?? '' ),
				'recaptcha_secret_key' => sanitize_text_field( $_POST['recaptcha_secret_key'] ?? '' ),
				'rate_limit_attempts' => absint( $_POST['rate_limit_attempts'] ?? 5 ),
				'rate_limit_window' => absint( $_POST['rate_limit_window'] ?? 60 ),
				'enable_device_fingerprint' => isset( $_POST['enable_device_fingerprint'] ),
				'enable_ip_tracking' => isset( $_POST['enable_ip_tracking'] ),
				'payment_timeout_warning' => absint( $_POST['payment_timeout_warning'] ?? 300 ),
				'enable_audit_logging' => isset( $_POST['enable_audit_logging'] ),
				'auto_close_expired_reservations' => isset( $_POST['auto_close_expired_reservations'] ),
			);

			update_option( 'twrf_settings', $settings );

			echo '<div class="notice notice-success is-dismissible"><p>' . 
				esc_html__( 'Settings saved successfully.', 'twrf' ) . 
			'</p></div>';

			Security::log_audit(
				get_current_user_id(),
				'settings_updated',
				'settings',
				null,
				null,
				$settings
			);
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Time Window Reservation Settings', 'twrf' ); ?></h1>

			<form method="post" action="">
				<?php wp_nonce_field( 'twrf_settings_action', 'twrf_settings_nonce' ); ?>

				<table class="form-table">
					<tbody>
						<!-- reCAPTCHA Settings -->
						<tr>
							<th scope="row">
								<label for="enable_recaptcha">
									<?php esc_html_e( 'Enable reCAPTCHA v3', 'twrf' ); ?>
								</label>
							</th>
							<td>
								<input type="checkbox" id="enable_recaptcha" name="enable_recaptcha" 
									<?php checked( $settings['enable_recaptcha'] ?? false ); ?>>
								<p class="description">
									<?php esc_html_e( 'Enable Google reCAPTCHA v3 for additional security.', 'twrf' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="recaptcha_site_key">
									<?php esc_html_e( 'reCAPTCHA Site Key', 'twrf' ); ?>
								</label>
							</th>
							<td>
								<input type="text" id="recaptcha_site_key" name="recaptcha_site_key" 
									value="<?php echo esc_attr( $settings['recaptcha_site_key'] ?? '' ); ?>" 
									class="regular-text">
								<p class="description">
									<?php esc_html_e( 'Get your keys from Google reCAPTCHA admin console.', 'twrf' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="recaptcha_secret_key">
									<?php esc_html_e( 'reCAPTCHA Secret Key', 'twrf' ); ?>
								</label>
							</th>
							<td>
								<input type="password" id="recaptcha_secret_key" name="recaptcha_secret_key" 
									value="<?php echo esc_attr( $settings['recaptcha_secret_key'] ?? '' ); ?>" 
									class="regular-text">
								<p class="description">
									<?php esc_html_e( 'Keep this secret and never share publicly.', 'twrf' ); ?>
								</p>
							</td>
						</tr>

						<!-- Rate Limiting -->
						<tr>
							<th scope="row" colspan="2">
								<h3><?php esc_html_e( 'Rate Limiting', 'twrf' ); ?></h3>
							</th>
						</tr>

						<tr>
							<th scope="row">
								<label for="rate_limit_attempts">
									<?php esc_html_e( 'Max Attempts', 'twrf' ); ?>
								</label>
							</th>
							<td>
								<input type="number" id="rate_limit_attempts" name="rate_limit_attempts" 
									value="<?php echo esc_attr( $settings['rate_limit_attempts'] ?? 5 ); ?>" 
									min="1" max="100">
								<p class="description">
									<?php esc_html_e( 'Maximum join attempts per user within the time window.', 'twrf' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="rate_limit_window">
									<?php esc_html_e( 'Rate Limit Window (seconds)', 'twrf' ); ?>
								</label>
							</th>
							<td>
								<input type="number" id="rate_limit_window" name="rate_limit_window" 
									value="<?php echo esc_attr( $settings['rate_limit_window'] ?? 60 ); ?>" 
									min="10" max="3600">
								<p class="description">
									<?php esc_html_e( 'Time window in seconds for rate limit counting.', 'twrf' ); ?>
								</p>
							</td>
						</tr>

						<!-- Security Features -->
						<tr>
							<th scope="row" colspan="2">
								<h3><?php esc_html_e( 'Security Features', 'twrf' ); ?></h3>
							</th>
						</tr>

						<tr>
							<th scope="row">
								<label for="enable_device_fingerprint">
									<?php esc_html_e( 'Enable Device Fingerprinting', 'twrf' ); ?>
								</label>
							</th>
							<td>
								<input type="checkbox" id="enable_device_fingerprint" name="enable_device_fingerprint" 
									<?php checked( $settings['enable_device_fingerprint'] ?? true ); ?>>
								<p class="description">
									<?php esc_html_e( 'Track device fingerprints to detect multi-account abuse.', 'twrf' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="enable_ip_tracking">
									<?php esc_html_e( 'Enable IP Tracking', 'twrf' ); ?>
								</label>
							</th>
							<td>
								<input type="checkbox" id="enable_ip_tracking" name="enable_ip_tracking" 
									<?php checked( $settings['enable_ip_tracking'] ?? true ); ?>>
								<p class="description">
									<?php esc_html_e( 'Track IP addresses to detect suspicious activity.', 'twrf' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="enable_audit_logging">
									<?php esc_html_e( 'Enable Audit Logging', 'twrf' ); ?>
								</label>
							</th>
							<td>
								<input type="checkbox" id="enable_audit_logging" name="enable_audit_logging" 
									<?php checked( $settings['enable_audit_logging'] ?? true ); ?>>
								<p class="description">
									<?php esc_html_e( 'Log all actions for security and compliance.', 'twrf' ); ?>
								</p>
							</td>
						</tr>

						<!-- Payment Settings -->
						<tr>
							<th scope="row" colspan="2">
								<h3><?php esc_html_e( 'Payment Settings', 'twrf' ); ?></h3>
							</th>
						</tr>

						<tr>
							<th scope="row">
								<label for="payment_timeout_warning">
									<?php esc_html_e( 'Payment Timeout Warning (seconds)', 'twrf' ); ?>
								</label>
							</th>
							<td>
								<input type="number" id="payment_timeout_warning" name="payment_timeout_warning" 
									value="<?php echo esc_attr( $settings['payment_timeout_warning'] ?? 300 ); ?>" 
									min="60" max="3600">
								<p class="description">
									<?php esc_html_e( 'Show warning when payment time is running out.', 'twrf' ); ?>
								</p>
							</td>
						</tr>

						<!-- Reservation Automation -->
						<tr>
							<th scope="row" colspan="2">
								<h3><?php esc_html_e( 'Automation', 'twrf' ); ?></h3>
							</th>
						</tr>

						<tr>
							<th scope="row">
								<label for="auto_close_expired_reservations">
									<?php esc_html_e( 'Auto-close Expired Reservations', 'twrf' ); ?>
								</label>
							</th>
							<td>
								<input type="checkbox" id="auto_close_expired_reservations" name="auto_close_expired_reservations" 
									<?php checked( $settings['auto_close_expired_reservations'] ?? true ); ?>>
								<p class="description">
									<?php esc_html_e( 'Automatically close and process reservations when time expires.', 'twrf' ); ?>
								</p>
							</td>
						</tr>
					</tbody>
				</table>

				<?php submit_button( __( 'Save Settings', 'twrf' ) ); ?>
			</form>

			<hr>

			<h2><?php esc_html_e( 'Algorithm Weights Reference', 'twrf' ); ?></h2>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Factor', 'twrf' ); ?></th>
						<th><?php esc_html_e( 'Default Weight', 'twrf' ); ?></th>
						<th><?php esc_html_e( 'Description', 'twrf' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php esc_html_e( 'Membership Duration', 'twrf' ); ?></td>
						<td>0.30</td>
						<td><?php esc_html_e( 'Reward long-term members (capped at 1 year)', 'twrf' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'No Recent Win Bonus', 'twrf' ); ?></td>
						<td>0.30</td>
						<td><?php esc_html_e( 'Reward users who haven\'t won recently (30 days)', 'twrf' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Join Time Weight', 'twrf' ); ?></td>
						<td>0.35</td>
						<td><?php esc_html_e( 'Reward users who joined early in the window', 'twrf' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Random Factor', 'twrf' ); ?></td>
						<td>0.05</td>
						<td><?php esc_html_e( 'Add unpredictability for fairness', 'twrf' ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}
}