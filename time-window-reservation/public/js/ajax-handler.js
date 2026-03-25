jQuery(document).ready(function($) {
	const nonce = twrf_data.nonce;
	const ajaxUrl = twrf_data.ajax_url;

	// Get user points
	function updateUserPoints() {
		$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: {
				action: 'twrf_get_user_points',
				nonce: nonce
			},
			success: function(response) {
				if (response.success) {
					$('.twrf-user-points .points-value').text(response.data.points);
				}
			}
		});
	}

	// Update on page load
	updateUserPoints();

	// Update every 30 seconds
	setInterval(updateUserPoints, 30000);
});