jQuery(document).ready(function($) {
	$('.twrf-countdown').each(function() {
		const $countdown = $(this);
		const productId = $countdown.data('product-id');
		const startTime = new Date($countdown.data('start')).getTime();
		const endTime = new Date($countdown.data('end')).getTime();

		function updateCountdown() {
			const now = new Date().getTime();
			let timeLeft;
			let message;

			if (now < startTime) {
				timeLeft = startTime - now;
				message = 'Reservation starts in: ';
			} else if (now < endTime) {
				timeLeft = endTime - now;
				message = 'Reservation ends in: ';
			} else {
				$countdown.find('.countdown-text').text(twrf_data.i18n ? twrf_data.i18n.closed : 'Reservation Closed');
				return;
			}

			const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
			const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
			const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
			const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

			let display = message;
			if (days > 0) {
				display += days + 'd ';
			}
			display += hours + 'h ' + minutes + 'm ' + seconds + 's';

			$countdown.find('.countdown-text').text(display);
		}

		updateCountdown();
		setInterval(updateCountdown, 1000);
	});
});