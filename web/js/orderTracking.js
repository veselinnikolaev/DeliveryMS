(function ($) {
    'use strict';

    function OrderTracking(config) {
        this.map = null;
        this.courierMarker = null;
        this.destinationMarker = null;
        this.routePath = null;
        this.updateTimer = null;
        this.config = config;

        if (config) {
            this.init();
        }
    }

    OrderTracking.prototype.init = function () {
        if ($('#deliveryMap').length) {
            this.initMap();
            // Geocode the delivery address with full details
            this.geocodeAddress(
                    this.config.deliveryAddress,
                    this.config.deliveryRegion,
                    this.config.deliveryCountry
                    );
        }
    };

    OrderTracking.prototype.initMap = function () {
        const defaultCenter = [43.2141, 27.9147]; // Varna coordinates

        this.map = L.map('deliveryMap').setView(defaultCenter, 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(this.map);

        this.courierMarker = L.marker(defaultCenter, {
            icon: L.divIcon({
                html: this.config.mapMarkers.courier,
                className: 'custom-div-icon courier-icon',
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            })
        }).addTo(this.map);

        this.destinationMarker = L.marker(defaultCenter, {
            icon: L.divIcon({
                html: this.config.mapMarkers.destination,
                className: 'custom-div-icon destination-icon',
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            })
        }).addTo(this.map);

        this.routePath = L.polyline([], {
            color: '#3388ff',
            weight: 3,
            opacity: 0.7,
            dashArray: '10, 10'
        }).addTo(this.map);

        this.updateCourierLocation();
        this.updateTimer = setInterval(() => {
            this.updateCourierLocation();
        }, 30000);
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
        if (response.success && response.latitude && response.longitude) {
            const courierPosition = [
                parseFloat(response.latitude),
                parseFloat(response.longitude)
            ];

            this.courierMarker.setLatLng(courierPosition);

            if (response.estimated_time) {
                $('#estimatedTime').text(response.estimated_time);
            }

            const updateTime = new Date(response.timestamp * 1000);
            const now = new Date();
            const minutesAgo = Math.floor((now - updateTime) / 60000);
            this.updateCourierStatus(minutesAgo);
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

    OrderTracking.prototype.geocodeAddress = function (address, region, country) {
        // Format the address in a way Nominatim understands better
        let formattedAddress = address
                .replace('boulevard', 'blvd')
                .replace('number', '')
                .replace(/\s+/g, ' ')
                .trim();

        // Construct the search query
        let searchQuery = `${formattedAddress}, ${region}, ${country}`;
        console.log('Searching for:', searchQuery);

        $.ajax({
            url: 'https://nominatim.openstreetmap.org/search',
            method: 'GET',
            data: {
                street: formattedAddress,
                city: region,
                country: country,
                format: 'json',
                limit: 1
            },
            headers: {
                'User-Agent': 'DeliveryTrackingSystem/1.0'
            },
            success: (response) => {
                console.log('Nominatim response:', response);

                if (response && response.length > 0) {
                    const position = [
                        parseFloat(response[0].lat),
                        parseFloat(response[0].lon)
                    ];
                    this.updateMapWithLocations(position);
                } else {
                    // Try alternative search
                    this.searchWithFullAddress(searchQuery);
                }
            },
            error: () => {
                this.searchWithFullAddress(searchQuery);
            }
        });
    };

    OrderTracking.prototype.searchWithFullAddress = function (fullAddress) {
        $.ajax({
            url: 'https://nominatim.openstreetmap.org/search',
            method: 'GET',
            data: {
                q: fullAddress,
                format: 'json',
                limit: 1
            },
            headers: {
                'User-Agent': 'DeliveryTrackingSystem/1.0'
            },
            success: (response) => {
                console.log('Full address search response:', response);

                if (response && response.length > 0) {
                    const position = [
                        parseFloat(response[0].lat),
                        parseFloat(response[0].lon)
                    ];
                    this.updateMapWithLocations(position);
                } else {
                    // Fall back to region center
                    this.useRegionCenter(this.config.deliveryRegion);
                }
            },
            error: () => {
                this.useRegionCenter(this.config.deliveryRegion);
            }
        });
    };
    
    OrderTracking.prototype.updateMapWithLocations = function (destinationPosition) {
        // Update destination marker
        this.destinationMarker.setLatLng(destinationPosition);
        this.destinationMarker.bindPopup(this.config.deliveryAddress).openPopup();

        // Get courier location
        this.updateCourierLocation();
    };

    OrderTracking.prototype.handleGeocodeError = function () {
        console.error('Geocoding failed, using region center as fallback');
        // Use region center as fallback
        this.getFallbackCoordinates(this.config.deliveryRegion);
    };

    OrderTracking.prototype.updateRoutePath = function (start, end) {
        this.routePath.setLatLngs([start, end]);
    };

    OrderTracking.prototype.fitMapBounds = function (start, end) {
        this.map.fitBounds([start, end], {
            padding: [50, 50]
        });
    };

    window.OrderTracking = OrderTracking;
})(jQuery);