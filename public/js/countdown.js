(function() {
    document.addEventListener('DOMContentLoaded', function() {
        const countdowns = document.querySelectorAll('.twrf-countdown');
        
        countdowns.forEach(function(element) {
            const endTimeStr = element.getAttribute('data-end-time');
            const status = element.getAttribute('data-status');
            
            if (!endTimeStr || (status !== 'active' && status !== 'upcoming')) {
                return;
            }
            
            // Parse datetime string
            const endTime = new Date(endTimeStr).getTime();
            
            if (isNaN(endTime)) {
                console.error('Invalid date format:', endTimeStr);
                return;
            }
            
            function updateCountdown() {
                const now = new Date().getTime();
                const distance = endTime - now;
                
                if (distance < 0) {
                    element.querySelector('.countdown-value').textContent = '00:00:00';
                    return;
                }
                
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                element.querySelector('.countdown-value').textContent = 
                    String(hours).padStart(2, '0') + ':' + 
                    String(minutes).padStart(2, '0') + ':' + 
                    String(seconds).padStart(2, '0');
            }
            
            updateCountdown();
            setInterval(updateCountdown, 1000);
        });
    });
})();
