(function($) {
    'use strict';

    // Check if twrf is defined (from wp_localize_script)
    if (typeof twrf === 'undefined') {
        console.error('TWRF: Global data not loaded');
        return;
    }

    $(document).ready(function() {
        
        // Join Reservation Button
        $('.twrf-join-btn').on('click', function(e) {
            e.preventDefault();
            
            const productId = $(this).data('product-id');
            
            $.ajax({
                url: twrf.ajax_url,
                type: 'POST',
                data: {
                    action: 'twrf_join_reservation',
                    product_id: productId,
                    nonce: twrf.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Successfully joined the reservation!');
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        });
    });
})(jQuery);
