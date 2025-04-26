class TrackingStateManager {
    static KEY = 'courier_tracking_state';
    static setTracking(isTracking) {
        localStorage.setItem(this.KEY, JSON.stringify({
            isTracking: isTracking,
            timestamp: Date.now()
        }));
    }

    static isTracking() {
        const state = localStorage.getItem(this.KEY);
        if (state) {
            const {isTracking, timestamp} = JSON.parse(state);
            // Consider tracking active if started within last 24 hours
            if (Date.now() - timestamp < 24 * 60 * 60 * 1000) {
                return isTracking;
            }
        }
        return false;
    }

    static clearTracking() {
        localStorage.removeItem(this.KEY);
    }
}

class TrackingControl {
    constructor() {
        this.map = null;
        this.currentMarker = null;
        this.isTracking = TrackingStateManager.isTracking();
        this.watchId = null;
        this.updateInterval = null;
        this.init();
        this.updateGlobalIndicator();
    }

    init() {
        console.log('Initializing tracking control...');

        // Check if we're on the tracking page
        const isTrackingPage = document.getElementById('tracking-map') !== null;

        if (isTrackingPage) {
            this.initializeMap();
            this.initializeTrackingButton();
        }

        // If tracking was active, restart it
        if (this.isTracking) {
            this.startTracking(true);
        }

        // Add page unload handler
        window.addEventListener('beforeunload', () => {
            // Only save tracking state if still tracking
            if (this.isTracking) {
                TrackingStateManager.setTracking(true);
            }
        });
    }

    initializeMap() {
        console.log('Initializing map...');
        this.map = L.map('tracking-map').setView([0, 0], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.map);
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                    (position) => {
                console.log('Got initial position:', position);
                const {latitude, longitude} = position.coords;
                this.map.setView([latitude, longitude], 13);
                this.updateMarker(latitude, longitude);
            },
                    (error) => this.handleError('Error getting initial position: ' + error.message)
            );
        }
    }

    initializeTrackingButton() {
        const toggleButton = document.getElementById('toggle-tracking');
        const indicator = document.getElementById('tracking-indicator');
        if (toggleButton && indicator) {
            // Update initial button state
            if (this.isTracking) {
                toggleButton.innerHTML = '<i class="mdi mdi-stop me-1"></i>Stop Tracking';
                toggleButton.classList.replace('btn-primary', 'btn-danger');
                indicator.classList.replace('bg-secondary', 'bg-success');
                indicator.textContent = 'Tracking Active';
            }

            toggleButton.addEventListener('click', () => {
                console.log('Toggle button clicked');
                this.toggleTracking();
            });
        }
    }

    startTracking(isRestore = false) {
        console.log('Starting tracking...');
        if (!navigator.geolocation) {
            this.handleError("Geolocation is not supported by your browser.");
            return;
        }

        this.watchId = navigator.geolocation.watchPosition(
                (position) => {
            const {latitude, longitude} = position.coords;

            // Only update marker if we're on the tracking page
            if (this.map && this.map._container) {
                this.updateMarker(latitude, longitude);
            }

            this.updateServerLocation(latitude, longitude);
        },
                (error) => this.handleError(error.message),
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
        );

        this.isTracking = true;

        if (!isRestore) {
            TrackingStateManager.setTracking(true);
        }

        this.updateGlobalIndicator();
    }

    stopTracking() {
        console.log('Stopping tracking...');
        if (this.watchId) {
            navigator.geolocation.clearWatch(this.watchId);
            this.watchId = null;
        }

        this.isTracking = false;
        TrackingStateManager.clearTracking();
        this.updateGlobalIndicator();
    }

    toggleTracking() {
        console.log('Toggling tracking. Current state:', this.isTracking);
        const button = document.getElementById('toggle-tracking');
        const indicator = document.getElementById('tracking-indicator');

        if (!this.isTracking) {
            this.startTracking();
            button.innerHTML = '<i class="mdi mdi-stop me-1"></i>Stop Tracking';
            button.classList.replace('btn-primary', 'btn-danger');
            indicator.classList.replace('bg-secondary', 'bg-success');
            indicator.textContent = 'Tracking Active';
        } else {
            this.stopTracking();
            button.innerHTML = '<i class="mdi mdi-crosshairs-gps me-1"></i>Start Tracking';
            button.classList.replace('btn-danger', 'btn-primary');
            indicator.classList.replace('bg-success', 'bg-secondary');
            indicator.textContent = 'Not tracking';
        }
    }

    updateMarker(lat, lng) {
        console.log('Updating marker position:', {lat, lng});
        if (!this.currentMarker) {
            this.currentMarker = L.marker([lat, lng], {
                icon: L.divIcon({
                    html: '<i class="mdi mdi-truck-fast text-primary" style="font-size: 24px;"></i>',
                    className: 'courier-marker'
                })
            }).addTo(this.map);
        } else {
            this.currentMarker.setLatLng([lat, lng]);
        }
        this.map.setView([lat, lng], this.map.getZoom());
    }

    updateServerLocation(latitude, longitude) {
        console.log('Sending location update to server:', {latitude, longitude});
        fetch('index.php?controller=Courier&action=updateLocation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `latitude=${latitude}&longitude=${longitude}`
        })
                .then(response => response.json())
                .then(data => {
                    console.log('Server response:', data);
                    if (data.status === 'error') {
                        this.handleError('Error updating location: ' + data.message);
                    }
                })
                .catch(error => {
                    this.handleError('Error sending location update: ' + error.message);
                });
    }

    handleError(message) {
        console.error('Error:', message);
        alert(message);
    }

    updateGlobalIndicator() {
        const globalIndicator = document.getElementById('global-tracking-indicator');
        if (globalIndicator) {
            if (this.isTracking) {
                if (globalIndicator.style.display !== 'flex') {
                    globalIndicator.style.display = 'flex';
                }
                globalIndicator.style.opacity = '1';
            } else {
                globalIndicator.style.opacity = '0';
                setTimeout(() => {
                    if (!this.isTracking) { // Check again in case it changed during timeout
                        globalIndicator.style.display = 'none';
                    }
                }, 300);
            }
        }
    }
}

// Initialize tracking control when document is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('Document ready, initializing tracking control...');

    // Always create the tracking control to handle tracking state
    const trackingControl = new TrackingControl();
});
