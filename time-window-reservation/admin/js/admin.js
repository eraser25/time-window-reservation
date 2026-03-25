jQuery(document).ready(function($) {
	const nonce = twrf_admin.nonce;
	const ajaxUrl = twrf_admin.ajax_url;

	// Load and display participant data
	$('.twrf-load-participants').on('click', function() {
		const reservationId = $(this).data('reservation-id');
		const $container = $(this).closest('tr').next('.twrf-participants-row');

		$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: {
				action: 'twrf_admin_get_participants',
				reservation_id: reservationId,
				nonce: nonce
			},
			success: function(response) {
				if (response.success) {
					let html = '<table class="wp-list-table widefat"><thead><tr><th>User</th><th>Join Time</th><th>Score</th><th>Status</th><th>Actions</th></tr></thead><tbody>';

					response.data.participants.forEach(function(p, index) {
						html += '<tr><td>' + p.user_id + '</td><td>' + p.join_timestamp + '</td><td>' + (p.score || '—') + '</td><td>' + (p.win_status || 'pending') + '</td><td><button class="button twrf-override" data-user-id="' + p.user_id + '">Override</button></td></tr>';
					});

					html += '</tbody></table>';
					$container.html(html);
				}
			}
		});
	});

	// Manual override
	$(document).on('click', '.twrf-override', function() {
		const userId = $(this).data('user-id');
		const productId = prompt('Enter Product ID:');

		if (!productId) return;

		$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: {
				action: 'twrf_admin_override_winner',
				user_id: userId,
				product_id: productId,
				action_type: 'manual_override',
				nonce: nonce
			},
			success: function(response) {
				if (response.success) {
					alert('Override applied successfully');
				}
			}
		});
	});
});