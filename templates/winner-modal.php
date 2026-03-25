<div id="twrf-winner-modal" class="twrf-modal" style="display: none;">
	<div class="twrf-modal-overlay"></div>
	<div class="twrf-modal-content">
		<div class="twrf-modal-header">
			<h2>🎉 <?php esc_html_e( 'Congratulations!', 'twrf' ); ?></h2>
			<button class="twrf-modal-close" aria-label="<?php esc_attr_e( 'Close', 'twrf' ); ?>">&times;</button>
		</div>

		<div class="twrf-modal-body">
			<p class="twrf-winner-message">
				<?php esc_html_e( 'You have been selected as a winner in the reservation draw!', 'twrf' ); ?>
			</p>

			<div class="twrf-product-info">
				<h3><?php esc_html_e( 'Your Prize:', 'twrf' ); ?></h3>
				<p class="product-name"></p>
			</div>

			<div class="twrf-payment-section">
				<h3><?php esc_html_e( 'Complete Your Purchase', 'twrf' ); ?></h3>
				<p><?php esc_html_e( 'You have a limited time to complete your purchase. The product has been added to your cart.', 'twrf' ); ?></p>

				<div class="twrf-countdown-container">
					<p><?php esc_html_e( 'Time Remaining:', 'twrf' ); ?></p>
					<div class="twrf-countdown-display">
						<span class="countdown-value"></span>
					</div>
					<p class="twrf-countdown-warning" style="display: none; color: #d32f2f;">
						<?php esc_html_e( '⚠️ Hurry! Your payment window is about to expire!', 'twrf' ); ?>
					</p>
				</div>

				<div class="twrf-score-info">
					<p>
						<strong><?php esc_html_e( 'Your Score:', 'twrf' ); ?></strong> 
						<span class="user-score"></span>
					</p>
					<p>
						<strong><?php esc_html_e( 'Your Rank:', 'twrf' ); ?></strong> 
						<span class="user-rank"></span>
					</p>
				</div>
			</div>
		</div>

		<div class="twrf-modal-footer">
			<button class="button button-secondary twrf-modal-close-btn">
				<?php esc_html_e( 'Remind Me Later', 'twrf' ); ?>
			</button>
			<a href="#" class="button button-primary twrf-proceed-checkout">
				<?php esc_html_e( 'Proceed to Checkout', 'twrf' ); ?>
			</a>
		</div>
	</div>
</div>

<style>
	.twrf-modal {
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		z-index: 999999;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.twrf-modal-overlay {
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background: rgba(0, 0, 0, 0.5);
		cursor: pointer;
	}

	.twrf-modal-content {
		position: relative;
		background: white;
		border-radius: 12px;
		box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
		max-width: 500px;
		width: 90%;
		max-height: 90vh;
		overflow-y: auto;
		animation: twrf-modal-slideIn 0.3s ease;
	}

	@keyframes twrf-modal-slideIn {
		from {
			opacity: 0;
			transform: translateY(-50px);
		}
		to {
			opacity: 1;
			transform: translateY(0);
		}
	}

	.twrf-modal-header {
		padding: 20px;
		border-bottom: 1px solid #eee;
		display: flex;
		justify-content: space-between;
		align-items: center;
	}

	.twrf-modal-header h2 {
		margin: 0;
		font-size: 24px;
		color: #28a745;
	}

	.twrf-modal-close {
		background: none;
		border: none;
		font-size: 28px;
		cursor: pointer;
		color: #999;
		padding: 0;
		width: 32px;
		height: 32px;
		display: flex;
		align-items: center;
		justify-content: center;
		border-radius: 4px;
		transition: all 0.2s;
	}

	.twrf-modal-close:hover {
		background: #f0f0f0;
		color: #333;
	}

	.twrf-modal-body {
		padding: 20px;
	}

	.twrf-winner-message {
		font-size: 16px;
		color: #333;
		margin-bottom: 20px;
		text-align: center;
	}

	.twrf-product-info {
		background: #f9f9f9;
		padding: 15px;
		border-radius: 8px;
		margin-bottom: 20px;
	}

	.twrf-product-info h3 {
		margin: 0 0 10px 0;
		font-size: 14px;
		color: #666;
		text-transform: uppercase;
	}

	.product-name {
		margin: 0;
		font-size: 18px;
		font-weight: bold;
		color: #007cba;
	}

	.twrf-payment-section h3 {
		margin-top: 0;
		font-size: 16px;
		color: #333;
	}

	.twrf-countdown-container {
		background: #fff3cd;
		border: 2px solid #ffc107;
		border-radius: 8px;
		padding: 15px;
		text-align: center;
		margin-bottom: 20px;
	}

	.twrf-countdown-container p:first-child {
		margin: 0 0 10px 0;
		font-weight: bold;
		color: #856404;
	}

	.twrf-countdown-display {
		font-size: 32px;
		font-weight: bold;
		color: #d32f2f;
		margin: 10px 0;
	}

	.countdown-value {
		font-family: 'Courier New', monospace;
	}

	.twrf-countdown-warning {
		margin: 10px 0 0 0 !important;
		font-weight: bold;
	}

	.twrf-score-info {
		background: #e7f3ff;
		padding: 12px;
		border-radius: 6px;
		margin-bottom: 20px;
		border-left: 4px solid #007cba;
	}

	.twrf-score-info p {
		margin: 6px 0;
		font-size: 14px;
	}

	.twrf-modal-footer {
		padding: 20px;
		border-top: 1px solid #eee;
		display: flex;
		gap: 10px;
		justify-content: flex-end;
	}

	.twrf-modal-footer .button {
		padding: 10px 20px;
		font-size: 14px;
	}

	.twrf-proceed-checkout {
		background: #28a745;
		border-color: #28a745;
		color: white;
	}

	.twrf-proceed-checkout:hover {
		background: #218838;
		border-color: #218838;
	}

	@media (max-width: 600px) {
		.twrf-modal-content {
			width: 95%;
		}

		.twrf-modal-footer {
			flex-direction: column;
		}

		.twrf-modal-footer .button {
			width: 100%;
		}

		.twrf-countdown-display {
			font-size: 24px;
		}
	}
</style>

<script>
(function() {
	const modal = document.getElementById('twrf-winner-modal');
	if (!modal) return;

	const closeBtn = modal.querySelector('.twrf-modal-close');
	const closeBtnFooter = modal.querySelector('.twrf-modal-close-btn');
	const overlay = modal.querySelector('.twrf-modal-overlay');
	const countdownDisplay = modal.querySelector('.countdown-value');
	const warning = modal.querySelector('.twrf-countdown-warning');
	let countdownInterval;

	function closeModal() {
		modal.style.display = 'none';
	}

	function updateCountdown(seconds) {
		const hours = Math.floor(seconds / 3600);
		const minutes = Math.floor((seconds % 3600) / 60);
		const secs = seconds % 60;

		let display = '';
		if (hours > 0) display += hours + 'h ';
		display += minutes + 'm ' + String(secs).padStart(2, '0') + 's';

		countdownDisplay.textContent = display;

		// Show warning if less than 5 minutes
		if (seconds < 300) {
			warning.style.display = 'block';
		}

		if (seconds <= 0) {
			clearInterval(countdownInterval);
			closeModal();
		}
	}

	function startCountdown(seconds) {
		updateCountdown(seconds);
		countdownInterval = setInterval(() => {
			seconds--;
			updateCountdown(seconds);
		}, 1000);
	}

	closeBtn.addEventListener('click', closeModal);
	closeBtnFooter.addEventListener('click', closeModal);
	overlay.addEventListener('click', closeModal);

	// Expose function to update modal from JS
	window.twrfShowWinnerModal = function(data) {
		if (data.product_name) {
			modal.querySelector('.product-name').textContent = data.product_name;
		}
		if (data.score) {
			modal.querySelector('.user-score').textContent = data.score.toFixed(4);
		}
		if (data.rank) {
			modal.querySelector('.user-rank').textContent = '#' + data.rank;
		}
		if (data.checkout_url) {
			modal.querySelector('.twrf-proceed-checkout').href = data.checkout_url;
		}
		if (data.deadline_seconds) {
			startCountdown(data.deadline_seconds);
		}

		modal.style.display = 'flex';
	};
})();
</script>