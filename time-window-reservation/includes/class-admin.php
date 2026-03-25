<?php
namespace TWRF;

class Admin {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menus' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_product_meta_boxes' ) );
		add_action( 'save_post_product', array( $this, 'save_product_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	public function register_menus() {
		add_menu_page(
			__( 'TWRF Settings', 'twrf' ),
			__( 'Time Window Reservation', 'twrf' ),
			'manage_woocommerce',
			'twrf-dashboard',
			array( $this, 'render_dashboard_page' ),
			'dashicons-calendar',
			56
		);

		add_submenu_page(
			'twrf-dashboard',
			__( 'Dashboard', 'twrf' ),
			__( 'Dashboard', 'twrf' ),
			'manage_woocommerce',
			'twrf-dashboard',
			array( $this, 'render_dashboard_page' )
		);

		add_submenu_page(
			'twrf-dashboard',
			__( 'Settings', 'twrf' ),
			__( 'Settings', 'twrf' ),
			'manage_woocommerce',
			'twrf-settings',
			array( $this, 'render_settings_page' )
		);

		add_submenu_page(
			'twrf-dashboard',
			__( 'Logs', 'twrf' ),
			__( 'Audit Logs', 'twrf' ),
			'manage_woocommerce',
			'twrf-logs',
			array( $this, 'render_logs_page' )
		);
	}

	public function add_product_meta_boxes() {
		add_meta_box(
			'twrf_product_reservation',
			__( 'Time Window Reservation Settings', 'twrf' ),
			array( $this, 'render_product_meta_box' ),
			'product',
			'normal',
			'high'
		);
	}

	public function render_product_meta_box( $post ) {
		wp_nonce_field( 'twrf_product_settings', 'twrf_product_nonce' );

		$product_id = $post->ID;
		$reservation = Reservation_Manager::get_product_reservation( $product_id );

		if ( ! $reservation ) {
			$reservation = (object) array(
				'reservation_start' => current_time( 'Y-m-d H:i' ),
				'reservation_end' => gmdate( 'Y-m-d H:i', time() + 600 ),
				'payment_duration' => 300,
				'stock_available' => 1,
				'backup_count' => 5,
				'points_reward' => 10,
				'algorithm_weights' => json_encode( array(
					'membership_weight' => 0.3,
					'no_recent_win_bonus' => 0.3,
					'join_time_weight' => 0.35,
					'random_factor' => 0.05,
				)),
				'cooldown_days' => 0,
			);
		}

		$weights = json_decode( $reservation->algorithm_weights, true );

		?>
		<div class="twrf-meta-box-container">
			<table class="form-table">
				<tr>
					<th><label for="twrf_reservation_start"><?php _e( 'Reservation Start', 'twrf' ); ?></label></th>
					<td><input type="datetime-local" id="twrf_reservation_start" name="twrf_reservation_start" value="<?php echo esc_attr( $reservation->reservation_start ); ?>"></td>
				</tr>
				<tr>
					<th><label for="twrf_reservation_end"><?php _e( 'Reservation End', 'twrf' ); ?></label></th>
					<td><input type="datetime-local" id="twrf_reservation_end" name="twrf_reservation_end" value="<?php echo esc_attr( $reservation->reservation_end ); ?>"></td>
				</tr>
				<tr>
					<th><label for="twrf_payment_duration"><?php _e( 'Payment Duration (seconds)', 'twrf' ); ?></label></th>
					<td><input type="number" id="twrf_payment_duration" name="twrf_payment_duration" value="<?php echo esc_attr( $reservation->payment_duration ); ?>" min="60"></td>
				</tr>
				<tr>
					<th><label for="twrf_stock_available"><?php _e( 'Stock Available', 'twrf' ); ?></label></th>
					<td><input type="number" id="twrf_stock_available" name="twrf_stock_available" value="<?php echo esc_attr( $reservation->stock_available ); ?>" min="1"></td>
				</tr>
				<tr>
					<th><label for="twrf_backup_count"><?php _e( 'Backup Count', 'twrf' ); ?></label></th>
					<td><input type="number" id="twrf_backup_count" name="twrf_backup_count" value="<?php echo esc_attr( $reservation->backup_count ); ?>" min="1"></td>
				</tr>
				<tr>
					<th><label for="twrf_points_reward"><?php _e( 'Points Reward for Non-Winners', 'twrf' ); ?></label></th>
					<td><input type="number" id="twrf_points_reward" name="twrf_points_reward" value="<?php echo esc_attr( $reservation->points_reward ); ?>" min="0"></td>
				</tr>
				<tr>
					<th><label for="twrf_cooldown_days"><?php _e( 'Cooldown Days (0 = no cooldown)', 'twrf' ); ?></label></th>
					<td><input type="number" id="twrf_cooldown_days" name="twrf_cooldown_days" value="<?php echo esc_attr( $reservation->cooldown_days ); ?>" min="0"></td>
				</tr>
				<tr>
					<th colspan="2"><h3><?php _e( 'Algorithm Weights', 'twrf' ); ?></h3></th>
				</tr>
				<tr>
					<th><label for="twrf_membership_weight"><?php _e( 'Membership Weight (0-1)', 'twrf' ); ?></label></th>
					<td><input type="number" id="twrf_membership_weight" name="twrf_membership_weight" value="<?php echo esc_attr( $weights['membership_weight'] ?? 0.3 ); ?>" min="0" max="1" step="0.1"></td>
				</tr>
				<tr>
					<th><label for="twrf_no_recent_win_bonus"><?php _e( 'No Recent Win Bonus (0-1)', 'twrf' ); ?></label></th>
					<td><input type="number" id="twrf_no_recent_win_bonus" name="twrf_no_recent_win_bonus" value="<?php echo esc_attr( $weights['no_recent_win_bonus'] ?? 0.3 ); ?>" min="0" max="1" step="0.1"></td>
				</tr>
				<tr>
					<th><label for="twrf_join_time_weight"><?php _e( 'Join Time Weight (0-1)', 'twrf' ); ?></label></th>
					<td><input type="number" id="twrf_join_time_weight" name="twrf_join_time_weight" value="<?php echo esc_attr( $weights['join_time_weight'] ?? 0.35 ); ?>" min="0" max="1" step="0.1"></td>
				</tr>
				<tr>
					<th><label for="twrf_random_factor"><?php _e( 'Random Factor % (0-1)', 'twrf' ); ?></label></th>
					<td><input type="number" id="twrf_random_factor" name="twrf_random_factor" value="<?php echo esc_attr( $weights['random_factor'] ?? 0.05 ); ?>" min="0" max="1" step="0.01"></td>
				</tr>
			</table>
		</div>
		<?php
	}

	public function save_product_settings( $post_id ) {
		if ( ! isset( $_POST['twrf_product_nonce'] ) || ! wp_verify_nonce( $_POST['twrf_product_nonce'], 'twrf_product_settings' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$args = array(
			'reservation_start' => isset( $_POST['twrf_reservation_start'] ) ? sanitize_text_field( $_POST['twrf_reservation_start'] ) : current_time( 'mysql' ),
			'reservation_end' => isset( $_POST['twrf_reservation_end'] ) ? sanitize_text_field( $_POST['twrf_reservation_end'] ) : gmdate( 'Y-m-d H:i:s', time() + 600 ),
			'payment_duration' => isset( $_POST['twrf_payment_duration'] ) ? absint( $_POST['twrf_payment_duration'] ) : 300,
			'stock_available' => isset( $_POST['twrf_stock_available'] ) ? absint( $_POST['twrf_stock_available'] ) : 1,
			'backup_count' => isset( $_POST['twrf_backup_count'] ) ? absint( $_POST['twrf_backup_count'] ) : 5,
			'points_reward' => isset( $_POST['twrf_points_reward'] ) ? absint( $_POST['twrf_points_reward'] ) : 10,
			'cooldown_days' => isset( $_POST['twrf_cooldown_days'] ) ? absint( $_POST['twrf_cooldown_days'] ) : 0,
			'algorithm_weights' => array(
				'membership_weight' => isset( $_POST['twrf_membership_weight'] ) ? floatval( $_POST['twrf_membership_weight'] ) : 0.3,
				'no_recent_win_bonus' => isset( $_POST['twrf_no_recent_win_bonus'] ) ? floatval( $_POST['twrf_no_recent_win_bonus'] ) : 0.3,
				'join_time_weight' => isset( $_POST['twrf_join_time_weight'] ) ? floatval( $_POST['twrf_join_time_weight'] ) : 0.35,
				'random_factor' => isset( $_POST['twrf_random_factor'] ) ? floatval( $_POST['twrf_random_factor'] ) : 0.05,
			),
		);

		$existing = Reservation_Manager::get_product_reservation( $post_id );

		if ( $existing ) {
			global $wpdb;
			$wpdb->update(
				$wpdb->prefix . 'twrf_reservations',
				array_merge(
					$args,
					array(
						'algorithm_weights' => json_encode( $args['algorithm_weights'] ),
						'updated_at' => current_time( 'mysql' ),
					)
				),
				array( 'product_id' => $post_id )
			);
		} else {
			Reservation_Manager::create_reservation( $post_id, $args );
		}

		Security::log_audit(
			get_current_user_id(),
			'reservation_settings_updated',
			'product',
			$post_id
		);
	}

	public function render_dashboard_page() {
		Dashboard::render();
	}

	public function render_settings_page() {
		Settings_Page::render();
	}

	public function render_logs_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( 'Unauthorized' );
		}

		global $wpdb;

		$paged = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
		$per_page = 50;
		$offset = ( $paged - 1 ) * $per_page;

		$logs = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}twrf_audit_logs ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$per_page,
				$offset
			)
		);

		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}twrf_audit_logs" );

		?>
		<div class="wrap">
			<h1><?php _e( 'Audit Logs', 'twrf' ); ?></h1>

			<table class="wp-list-table widefat striped">
				<thead>
					<tr>
						<th><?php _e( 'User', 'twrf' ); ?></th>
						<th><?php _e( 'Action', 'twrf' ); ?></th>
						<th><?php _e( 'Object', 'twrf' ); ?></th>
						<th><?php _e( 'IP Address', 'twrf' ); ?></th>
						<th><?php _e( 'Date', 'twrf' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( (array) $logs as $log ) : ?>
						<tr>
							<td><?php echo esc_html( $log->user_id ? get_user_by( 'id', $log->user_id )->display_name : '—' ); ?></td>
							<td><?php echo esc_html( $log->action ); ?></td>
							<td><?php echo esc_html( $log->object_type . ' #' . $log->object_id ); ?></td>
							<td><?php echo esc_html( $log->ip_address ); ?></td>
							<td><?php echo esc_html( $log->created_at ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php
			$total_pages = ceil( $total / $per_page );
			echo paginate_links( array(
				'total' => $total_pages,
				'current' => $paged,
			));
			?>
		</div>
		<?php
	}

	public function enqueue_admin_assets() {
		if ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'twrf' ) !== false ) {
			wp_enqueue_style(
				'twrf-admin',
				TWRF_PLUGIN_URL . 'admin/css/admin.css',
				array(),
				TWRF_VERSION
			);

			wp_enqueue_script(
				'twrf-admin',
				TWRF_PLUGIN_URL . 'admin/js/admin.js',
				array( 'jquery' ),
				TWRF_VERSION,
				true
			);

			wp_localize_script(
				'twrf-admin',
				'twrf_admin',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'twrf_admin_nonce' ),
				)
			);
		}
	}
}