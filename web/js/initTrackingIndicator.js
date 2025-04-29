(function () {
    try {
        const state = JSON.parse(localStorage.getItem('courier_tracking_state'));
        const isTracking = state && state.isTracking && (Date.now() - state.timestamp < 24 * 60 * 60 * 1000);
        if (!isTracking) {
            const style = document.createElement('style');
            style.innerHTML = '#global-tracking-indicator { display: none !important; opacity: 0 !important; }';
            document.head.appendChild(style);
        }
    } catch (e) {
        const style = document.createElement('style');
        style.innerHTML = '#global-tracking-indicator { display: none !important; opacity: 0 !important; }';
        document.head.appendChild(style);
    }
})(jQuery);