<?php
/**
 * Admin Dashboard Template
 * Displays dashboard statistics and information
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Time Window Reservation Dashboard', 'twrf' ); ?></h1>

	<!-- Statistics Cards -->
	<div class="twrf-stats-grid">
		<div class="stat-card">
			<div class="stat-icon">📅</div>
			<div class="stat-content">
				<h3><?php esc_html_e( 'Total Reservations', 'twrf' ); ?></h3>
				<p class="stat-value"><?php echo esc_html( $total_reservations ); ?></p>
			</div>
		</div>

		<div class="stat-card">
			<div class="stat-icon">👥</div>
			<div class="stat-content">
				<h3><?php esc_html_e( 'Total Participants', 'twrf' ); ?></h3>
				<p class="stat-value"><?php echo esc_html( $total_participants ); ?></p>
			</div>
		</div>

		<div class="stat-card">
			<div class="stat-icon">🏆</div>
			<div class="stat-content">
				<h3><?php esc_html_e( 'Winners', 'twrf' ); ?></h3>
				<p class="stat-value"><?php echo esc_html( $total_winners ); ?></p>
			</div>
		</div>

		<div class="stat-card">
			<div class="stat-icon">⭐</div>
			<div class="stat-content">
				<h3><?php esc_html_e( 'Backup Winners', 'twrf' ); ?></h3>
				<p class="stat-value"><?php echo esc_html( $total_backups ); ?></p>
			</div>
		</div>

		<div class="stat-card">
			<div class="stat-icon">💰</div>
			<div class="stat-content">
				<h3><?php esc_html_e( 'Points Issued', 'twrf' ); ?></h3>
				<p class="stat-value"><?php echo esc_html( $total_points_issued ); ?></p>
			</div>
		</div>
	</div>

	<!-- Active Reservations -->
	<div class="twrf-section">
		<h2><?php esc_html_e( 'Active Reservations', 'twrf' ); ?></h2>
		<?php if ( ! empty( $active ) ) : ?>
			<table class="wp-list-table widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Product', 'twrf' ); ?></th>
						<th><?php esc_html_e( 'Starts', 'twrf' ); ?></th>
						<th><?php esc_html_e( 'Ends', 'twrf' ); ?></th>
						<th><?php esc_html_e( 'Status', 'twrf' ); ?></th>
						<th><?php esc_html_e( 'Stock', 'twrf' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'twrf' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $active as $res ) : ?>
						<tr>
							<td>
								<a href="<?php echo esc_url( get_edit_post_link( $res->product_id ) ); ?>">
									<?php echo esc_html( $res->product_name ); ?>
								</a>
							</td>
							<td><?php echo esc_html( $res->reservation_start ); ?></td>
							<td><?php echo esc_html( $res->reservation_end ); ?></td>
							<td><span class="badge badge-active"><?php echo esc_html( $res->status ); ?></span></td>
							<td><?php echo esc_html( $res->stock_available ); ?></td>
							<td>
								<a href="<?php echo esc_url( add_query_arg( 'reservation_id', $res->id ) ); ?>" class="button button-small">
									<?php esc_html_e( 'View Details', 'twrf' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<p><?php esc_html_e( 'No active reservations.', 'twrf' ); ?></p>
		<?php endif; ?>
	</div>

	<!-- Recent Reservations -->
	<div class="twrf-section">
		<h2><?php esc_html_e( 'Recent Reservations', 'twrf' ); ?></h2>
		<?php if ( ! empty( $reservations ) ) : ?>
			<table class="wp-list-table widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Product', 'twrf' ); ?></th>
						<th><?php esc_html_e( 'Reservation Window', 'twrf' ); ?></th>
						<th><?php esc_html_e( 'Status', 'twrf' ); ?></th>
						<th><?php esc_html_e( 'Participants', 'twrf' ); ?></th>
						<th><?php esc_html_e( 'Stock', 'twrf' ); ?></th>
						<th><?php esc_html_e( 'Created', 'twrf' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $reservations as $res ) : ?>
						<tr>
							<td><?php echo esc_html( $res->product_name ); ?></td>
							<td>
								<?php echo esc_html( $res->reservation_start ); ?> - 
								<?php echo esc_html( $res->reservation_end ); ?>
							</td>
							<td><span class="badge badge-<?php echo esc_attr( $res->status ); ?>"><?php echo esc_html( $res->status ); ?></span></td>
							<td><?php echo esc_html( $res->participant_count ); ?></td>
							<td><?php echo esc_html( $res->stock_available ); ?></td>
							<td><?php echo esc_html( $res->created_at ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<p><?php esc_html_e( 'No reservations found.', 'twrf' ); ?></p>
		<?php endif; ?>
	</div>

	<!-- Top Participants -->
	<div class="twrf-section twrf-half">
		<h2><?php esc_html_e( 'Top Participants', 'twrf' ); ?></h2>
		<?php 
		$top_participants = TWRF\Dashboard::get_top_participants( 5 );
		if ( ! empty( $top_participants ) ) : 
		?>
			<table class="wp-list-table widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'User', 'twrf' ); ?></th>
						<th><?php esc_html_e( 'Wins', 'twrf' ); ?></th>
						<th><?php esc_html_e( 'Total Score', 'twrf' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $top_participants as $user ) : ?>
						<tr>
							<td><?php echo esc_html( $user->display_name ); ?></td>
							<td><?php echo esc_html( $user->win_count ); ?></td>
							<td><?php echo esc_html( number_format( $user->total_score, 2 ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<p><?php esc_html_e( 'No participant data yet.', 'twrf' ); ?></p>
		<?php endif; ?>
	</div>

	<!-- Point Distribution -->
	<div class="twrf-section twrf-half">
		<h2><?php esc_html_e( 'Recent Points Distribution', 'twrf' ); ?></h2>
		<?php 
		$points_data = TWRF\Dashboard::get_point_distribution();
		if ( ! empty( $points_data ) ) : 
		?>
			<table class="wp-list-table widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Date', 'twrf' ); ?></th>
						<th><?php esc_html_e( 'Net Points', 'twrf' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $points_data as $row ) : ?>
						<tr>
							<td><?php echo esc_html( $row->date ); ?></td>
							<td><?php echo esc_html( number_format( $row->net_points, 0 ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<p><?php esc_html_e( 'No point data yet.', 'twrf' ); ?></p>
		<?php endif; ?>
	</div>
</div>

<style>
	.twrf-stats-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
		gap: 20px;
		margin-bottom: 40px;
	}

	.stat-card {
		background: white;
		padding: 20px;
		border: 1px solid #ccc;
		border-radius: 8px;
		box-shadow: 0 2px 4px rgba(0,0,0,0.1);
		display: flex;
		align-items: center;
		gap: 15px;
	}

	.stat-icon {
		font-size: 32px;
	}

	.stat-content h3 {
		margin: 0;
		font-size: 12px;
		color: #666;
		text-transform: uppercase;
	}

	.stat-value {
		margin: 5px 0 0 0;
		font-size: 28px;
		font-weight: bold;
		color: #007cba;
	}

	.twrf-section {
		background: white;
		padding: 20px;
		border: 1px solid #ccc;
		border-radius: 8px;
		margin-bottom: 20px;
	}

	.twrf-section h2 {
		margin-top: 0;
	}

	.twrf-half {
		display: inline-block;
		width: calc(50% - 10px);
		margin-right: 20px;
		vertical-align: top;
	}

	.badge {
		display: inline-block;
		padding: 4px 8px;
		border-radius: 4px;
		font-size: 12px;
		font-weight: bold;
	}

	.badge-active {
		background: #d4edda;
		color: #155724;
	}

	.badge-completed {
		background: #cfe2ff;
		color: #084298;
	}

	.badge-calculating {
		background: #fff3cd;
		color: #856404;
	}
</style>