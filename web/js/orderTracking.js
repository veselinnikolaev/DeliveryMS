(function ($) {
    'use strict';

    function OrderTracking(config) {
        // Initialize properties
        this.map = null;
        this.courierMarker = null;
        this.destinationMarker = null;
        this.routePath = null;
        this.updateTimer = null;
        this.config = config;

        // Start initialization if config is provided
        if (config) {
            this.init();
        }
    }

    OrderTracking.prototype.init = function () {
        // Initialize map only if the container exists
        if ($('#deliveryMap').length) {
            this.initMap();
            this.setupEventListeners();
        } else {
            console.error('Map container not found');
        }
    };

    OrderTracking.prototype.initMap = function () {
        // Default center (can be adjusted based on your primary service area)
        const defaultCenter = [42.6977, 23.3219]; // Sofia, Bulgaria coordinates

        // Create the map instance
        this.map = L.map('deliveryMap').setView(defaultCenter, 12);

        // Add the tile layer (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(this.map);

        // Create courier marker
        const courierIcon = L.divIcon({
            html: this.config.mapMarkers.courier,
            className: 'custom-div-icon courier-icon',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        });

        this.courierMarker = L.marker(defaultCenter, {
            icon: courierIcon
        }).addTo(this.map);
        this.courierMarker.bindPopup("Courier Location");

        // Create destination marker
        const destinationIcon = L.divIcon({
            html: this.config.mapMarkers.destination,
            className: 'custom-div-icon destination-icon',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        });

        this.destinationMarker = L.marker(defaultCenter, {
            icon: destinationIcon
        }).addTo(this.map);
        this.destinationMarker.bindPopup("Delivery Destination");

        // Create route path
        this.routePath = L.polyline([], {
            color: '#3388ff',
            weight: 3,
            opacity: 0.7,
            dashArray: '10, 10'
        }).addTo(this.map);

        // Initial location update
        this.updateCourierLocation();

        // Start periodic updates
        this.updateTimer = setInterval(() => {
            this.updateCourierLocation();
        }, 30000); // Update every 30 seconds
    };

    OrderTracking.prototype.updateCourierLocation = function () {
        $.ajax({
            url: 'index.php?controller=Courier&action=getCourierLocation',
            type: 'POST',
            dataType: 'json',
            data: {
                user_id: this.config.courierId,
                order_id: this.config.orderId
            },
            success: (response) => this.handleLocationUpdate(response),
            error: () => this.handleLocationError()
        });
    };

    OrderTracking.prototype.handleLocationUpdate = function (response) {
        if (response.success) {
            const courierPosition = [
                parseFloat(response.latitude),
                parseFloat(response.longitude)
            ];

            // Update courier marker position
            this.courierMarker.setLatLng(courierPosition);

            // Update estimated delivery time
            if (response.estimated_time) {
                $('#estimatedTime').text(response.estimated_time);
            }

            // Calculate and display time since last update
            const updateTime = new Date(response.timestamp * 1000);
            const now = new Date();
            const minutesAgo = Math.floor((now - updateTime) / 60000);
            this.updateCourierStatus(minutesAgo);

            // If destination hasn't been set yet, geocode the delivery address
            if (!this.destinationMarker.getLatLng() ||
                    (this.destinationMarker.getLatLng().lat === 42.6977 &&
                            this.destinationMarker.getLatLng().lng === 23.3219)) {
                this.geocodeAddress(this.config.deliveryAddress);
            } else {
                // Update route and fit bounds
                this.updateRoutePath(courierPosition, this.destinationMarker.getLatLng());
                this.fitMapBounds(courierPosition, this.destinationMarker.getLatLng());
            }
        } else {
            this.handleLocationError();
        }
    };

    OrderTracking.prototype.handleLocationError = function () {
        $('#courierStatus').text('Unable to update location');
        $('#estimatedTime').text('Not available');
    };

    OrderTracking.prototype.updateCourierStatus = function (minutesAgo) {
        let status;
        if (minutesAgo < 5) {
            status = 'Location updated just now';
        } else if (minutesAgo < 60) {
            status = `Location updated ${minutesAgo} minutes ago`;
        } else {
            const hours = Math.floor(minutesAgo / 60);
            status = `Location updated ${hours} hour${hours > 1 ? 's' : ''} ago`;
        }
        $('#courierStatus').text(status);
    };

    OrderTracking.prototype.geocodeAddress = function (address) {
        $.ajax({
            url: `https://nominatim.openstreetmap.org/search`,
            method: 'GET',
            data: {
                format: 'json',
                q: address,
                limit: 1
            },
            headers: {
                'User-Agent': 'DeliveryTrackingSystem/1.0'
            },
            success: (response) => {
                if (response && response[0]) {
                    const position = [
                        parseFloat(response[0].lat),
                        parseFloat(response[0].lon)
                    ];
                    this.destinationMarker.setLatLng(position);

                    if (this.courierMarker) {
                        const courierPos = this.courierMarker.getLatLng();
                        this.updateRoutePath([courierPos.lat, courierPos.lng], position);
                        this.fitMapBounds([courierPos.lat, courierPos.lng], position);
                    }
                }
            },
            error: () => {
                console.error('Geocoding failed');
            }
        });
    };

    OrderTracking.prototype.updateRoutePath = function (start, end) {
        this.routePath.setLatLngs([start, end]);
    };

    OrderTracking.prototype.fitMapBounds = function (start, end) {
        const bounds = L.latLngBounds([start, end]);
        this.map.fitBounds(bounds, {
            padding: [50, 50],
            maxZoom: 15
        });
    };

    OrderTracking.prototype.setupEventListeners = function () {
        // Clean up on page unload
        $(window).on('beforeunload', () => {
            if (this.updateTimer) {
                clearInterval(this.updateTimer);
            }
        });

        // Handle map resize
        $(window).on('resize', () => {
            if (this.map) {
                this.map.invalidateSize();
            }
        });
    };

    // Make OrderTracking available globally
    window.OrderTracking = OrderTracking;
})(jQuery);