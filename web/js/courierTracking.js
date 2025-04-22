class CourierTracking {
    constructor() {
        this.map = null;
        this.courierMarker = null;
        this.destinationMarker = null;
        this.routeLine = null;
        this.watchId = null;
        this.isCourier = document.body.dataset.courierId !== '';
        this.mapLoaded = false;
        this.updateInterval = null;
        this.addRouteStyles();
    }

    async init() {
        try {
            await this.initializeMap();
            await this.initializeDestinationMarker();
            this.startCourierTracking();
        } catch (error) {
            console.error('Initialization error:', error);
            this.handleError('Error initializing tracking system');
        }
    }

    async initializeMap() {
        this.map = L.map('deliveryMap').setView([0, 0], 13);
        await new Promise((resolve) => {
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(this.map).on('load', resolve);
        });
        this.mapLoaded = true;
    }

    async initializeDestinationMarker() {
        const deliveryAddressElement = document.getElementById('deliveryAddress');
        const address = deliveryAddressElement.dataset.address;
        if (address) {
            const coords = await this.geocodeAddress(address);
            this.destinationMarker = L.marker([coords.lat, coords.lng], {
                icon: L.divIcon({
                    html: '<i class="mdi mdi-map-marker text-danger" style="font-size: 24px;"></i>',
                    className: 'destination-marker'
                })
            }).addTo(this.map);
            this.map.setView([coords.lat, coords.lng], 13);
        }
    }

    startCourierTracking() {
        const deliveryAddressElement = document.getElementById('deliveryAddress');
        const courierId = deliveryAddressElement.dataset.courierId;
        if (!courierId) {
            this.handleError('No courier assigned');
            return;
        }

// Initial update
        this.updateCourierLocation(courierId);
        // Set up regular updates
        this.updateInterval = setInterval(() => {
            this.updateCourierLocation(courierId);
        }, 10000); // Update every 10 seconds
    }

    async updateCourierLocation(courierId) {
        try {
            const response = await fetch(`index.php?controller=Courier&action=getLocation&courier_id=${courierId}`);
            const data = await response.json();
            if (data.status === 'success') {
                const {latitude, longitude, timestamp} = data.data;
                this.updateCourierMarker(latitude, longitude);
                this.updateRouteAndInfo(latitude, longitude, timestamp);
            } else {
                this.handleError('Waiting for courier location...');
            }
        } catch (error) {
            console.error('Error fetching courier location:', error);
            this.handleError('Error updating courier location');
        }
    }

    updateCourierMarker(lat, lng) {
        if (!this.courierMarker) {
            this.courierMarker = L.marker([lat, lng], {
                icon: L.divIcon({
                    html: '<i class="mdi mdi-truck-fast text-primary" style="font-size: 24px;"></i>',
                    className: 'courier-marker'
                })
            }).addTo(this.map);
        } else {
            this.courierMarker.setLatLng([lat, lng]);
        }

// Fit bounds to show both markers
        if (this.destinationMarker) {
            const group = new L.featureGroup([this.courierMarker, this.destinationMarker]);
            this.map.fitBounds(group.getBounds(), {padding: [50, 50]});
        }
    }

    async updateRouteAndInfo(courierLat, courierLng, timestamp) {
        if (!this.destinationMarker)
            return;
        const destLatLng = this.destinationMarker.getLatLng();
        try {
            const response = await fetch(
                    `https://router.project-osrm.org/route/v1/driving/` +
                    `${courierLng},${courierLat};${destLatLng.lng},${destLatLng.lat}` +
                    `?overview=full&geometries=geojson`
                    );
            const data = await response.json();
            if (data.routes && data.routes[0]) {
                // Update route on map
                if (this.routeLine) {
                    this.map.removeLayer(this.routeLine);
                }
                this.routeLine = L.geoJSON(data.routes[0].geometry).addTo(this.map);
                // Update estimated time
                const duration = Math.round(data.routes[0].duration / 60);
                document.getElementById('estimatedTime').innerHTML = `${duration} minutes`;
                // Update courier status
                const timeDiff = Math.round((Date.now() / 1000 - timestamp) / 60);
                const status = timeDiff < 5 ? 'Active' : `Last seen ${timeDiff} minutes ago`;
                document.getElementById('courierStatus').innerHTML = status;
            }
        } catch (error) {
            console.error('Error updating route:', error);
            this.handleError('Error calculating route');
        }
    }

    async geocodeAddress(address) {
        try {
            const response = await fetch(
                    `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`
                    );
            const data = await response.json();
            if (data && data.length > 0) {
                return {
                    lat: parseFloat(data[0].lat),
                    lng: parseFloat(data[0].lon)
                };
            }
            throw new Error('Address not found');
        } catch (error) {
            console.error('Geocoding error:', error);
            this.handleError('Error finding address location');
            return {lat: 0, lng: 0};
        }
    }

    handleError(message) {
        document.getElementById('estimatedTime').innerHTML = 'Not available';
        document.getElementById('courierStatus').innerHTML = message;
    }

    removeLoader() {
        const loadingOverlay = document.querySelector('.map-loading-overlay');
        if (loadingOverlay) {
            loadingOverlay.style.opacity = '0';
            setTimeout(() => loadingOverlay.remove(), 300);
        }
    }

    async updateRouteAndInfo(courierLat, courierLng, timestamp) {
        if (!this.destinationMarker)
            return;
        const destLatLng = this.destinationMarker.getLatLng();
        try {
            // Get route from OSRM
            const response = await fetch(
                    `https://router.project-osrm.org/route/v1/driving/` +
                    `${courierLng},${courierLat};${destLatLng.lng},${destLatLng.lat}` +
                    `?overview=full&geometries=geojson&steps=true`
                    );
            const data = await response.json();
            if (data.routes && data.routes[0]) {
                this.drawRoute(data.routes[0].geometry);
                this.updateDeliveryInfo(data.routes[0], timestamp);
            }
        } catch (error) {
            console.error('Error updating route:', error);
            this.handleError('Error calculating route');
        }
    }

    drawRoute(geometry) {
        // Remove existing route if any
        if (this.routeLine) {
            this.map.removeLayer(this.routeLine);
        }

        // Create new route with custom style
        this.routeLine = L.geoJSON(geometry, {
            style: {
                color: '#4CAF50',
                weight: 4,
                opacity: 0.8,
                lineJoin: 'round',
                lineCap: 'round',
                dashArray: '10, 10',
                className: 'delivery-route'
            }
        }).addTo(this.map);
        // Add direction arrows
        this.addRouteArrows(geometry);
        // Fit map bounds to show entire route
        const bounds = this.routeLine.getBounds();
        this.map.fitBounds(bounds, {
            padding: [50, 50],
            maxZoom: 15
        });
    }

    addRouteArrows(geometry) {
        if (!geometry.coordinates || geometry.coordinates.length < 2)
            return;
        // Create arrow symbols along the route
        const coordinates = geometry.coordinates;
        for (let i = 0; i < coordinates.length - 1; i += Math.ceil(coordinates.length / 10)) {
            const start = coordinates[i];
            const end = coordinates[i + 1];
            if (start && end) {
                const arrowIcon = L.divIcon({
                    html: '→',
                    className: 'route-arrow',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });
                // Calculate middle point for arrow placement
                const lat = (start[1] + end[1]) / 2;
                const lng = (start[0] + end[0]) / 2;
                // Calculate rotation angle
                const angle = this.calculateAngle(
                        {lat: start[1], lng: start[0]},
                        {lat: end[1], lng: end[0]}
                );
                // Add arrow marker
                L.marker([lat, lng], {
                    icon: arrowIcon,
                    rotationAngle: angle
                }).addTo(this.map);
            }
        }
    }

    calculateAngle(start, end) {
        return Math.atan2(end.lng - start.lng, end.lat - start.lat) * 180 / Math.PI;
    }

    updateDeliveryInfo(route, timestamp) {
        // Update estimated time
        const duration = Math.round(route.duration / 60);
        const distance = (route.distance / 1000).toFixed(1);
        document.getElementById('estimatedTime').innerHTML =
                `${duration} minutes (${distance} km)`;
        // Update courier status
        const timeDiff = Math.round((Date.now() / 1000 - timestamp) / 60);
        const status = timeDiff < 5
                ? `Active - On route`
                : `Last seen ${timeDiff} minutes ago`;
        document.getElementById('courierStatus').innerHTML = status;
    }

    // Add these helper styles when the class is initialized
    addRouteStyles() {
        if (!document.getElementById('route-styles')) {
            const style = document.createElement('style');
            style.id = 'route-styles';
            style.textContent = `
                .route-arrow {
                    color: #4CAF50;
                    font-size: 16px;
                    font-weight: bold;
                    text-shadow: 2px 2px 2px rgba(255,255,255,0.8);
                }
                .delivery-route {
                    stroke-dasharray: 10, 10;
                    animation: dash 30s linear infinite;
                }
                @keyframes dash {
                    to {
                        stroke-dashoffset: -200;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }

}

// Initialize tracking
document.addEventListener('DOMContentLoaded', () => {
    const mapContainer = document.getElementById('deliveryMap');
    if (mapContainer) {
        const tracking = new CourierTracking();
        tracking.init().then(() => {
            tracking.removeLoader();
        }).catch(error => {
            console.error('Error initializing tracking:', error);
        });
    }
});