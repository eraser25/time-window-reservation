jQuery(document).ready(function($) {
	const nonce = twrf_data.nonce;
	const ajaxUrl = twrf_data.ajax_url;

	// Join Reservation
	$('.btn-join-reservation').on('click', function(e) {
		e.preventDefault();

		const productId = $(this).data('product-id');
		const $btn = $(this);
		const $container = $btn.closest('.twrf-reservation-button-container');

		// Get device fingerprint if enabled
		const deviceFingerprint = typeof getDeviceFingerprint !== 'undefined' ? getDeviceFingerprint() : '';

		$btn.prop('disabled', true).text(twrf_data.i18n ? twrf_data.i18n.joining : 'Joining...');

		$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: {
				action: 'twrf_join_reservation',
				product_id: productId,
				device_fingerprint: deviceFingerprint,
				nonce: nonce
			},
			success: function(response) {
				if (response.success) {
					showMessage($container, response.data.message, 'success');
					$btn.hide();

					// Show payment window if user is a winner
					setTimeout(function() {
						checkIfWinner(productId, $container);
					}, 1000);
				} else {
					showMessage($container, response.data.message, 'error');
					$btn.prop('disabled', false).text(twrf_data.i18n ? twrf_data.i18n.join : 'Join Reservation');
				}
			},
			error: function() {
				showMessage($container, 'An error occurred', 'error');
				$btn.prop('disabled', false).text(twrf_data.i18n ? twrf_data.i18n.join : 'Join Reservation');
			}
		});
	});

	// Update participant count
	setInterval(function() {
		$('.twrf-participant-count').each(function() {
			const productId = $(this).data('product-id');
			const $count = $(this).find('.count');

			$.ajax({
				url: ajaxUrl,
				type: 'POST',
				data: {
					action: 'twrf_get_participant_count',
					product_id: productId,
					nonce: nonce
				},
				success: function(response) {
					if (response.success) {
						$count.text(response.data.count);
					}
				}
			});
		});
	}, 5000); // Update every 5 seconds

	function checkIfWinner(productId, $container) {
		$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: {
				action: 'twrf_get_time_until_deadline',
				product_id: productId,
				nonce: nonce
			},
			success: function(response) {
				if (response.success) {
					showWinnerModal(productId, response.data.seconds, $container);
				}
			}
		});
	}

	function showWinnerModal(productId, seconds, $container) {
		const $modal = $('<div class="twrf-winner-modal"></div>')
			.html('<div class="modal-content"><h2>🎉 Congratulations! You Won!</h2><p>You have <span class="deadline-counter"></span> to complete your purchase.</p><button class="button button-primary proceed-to-checkout">Go to Checkout</button></div>');

		$container.append($modal);

		let timeLeft = seconds;

		function updateDeadline() {
			const hours = Math.floor(timeLeft / 3600);
			const minutes = Math.floor((timeLeft % 3600) / 60);
			const secs = timeLeft % 60;

			let display = '';
			if (hours > 0) display += hours + 'h ';
			display += minutes + 'm ' + secs + 's';

			$modal.find('.deadline-counter').text(display);

			if (timeLeft > 0) {
				timeLeft--;
				setTimeout(updateDeadline, 1000);
			} else {
				$modal.find('.modal-content').html('<p>Your payment period has expired.</p>');
			}
		}

		updateDeadline();

		$modal.find('.proceed-to-checkout').on('click', function() {
			$.ajax({
				url: ajaxUrl,
				type: 'POST',
				data: {
					action: 'twrf_add_to_cart_payment',
					product_id: productId,
					nonce: nonce
				},
				success: function(response) {
					if (response.success) {
						window.location.href = response.data.cart_url;
					}
				}
			});
		});
	}

	function showMessage($container, message, type) {
		const $message = $('<div class="twrf-message ' + type + '">' + message + '</div>');
		$container.prepend($message);

		setTimeout(function() {
			$message.fadeOut(function() {
				$(this).remove();
			});
		}, 5000);
	}
});