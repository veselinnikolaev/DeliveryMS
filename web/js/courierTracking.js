class CourierTracking {
    constructor() {
        this.map = null;
        this.courierMarker = null;
        this.destinationMarker = null;
        this.routeLine = null;
        this.watchId = null; // Note: this.watchId seems unused in the provided code
        // this.isCourier = document.body.dataset.courierId !== ''; // Note: this.isCourier seems unused
        this.mapLoaded = false;
        this.updateInterval = null;
    }

    async init() {
        try {
// Ensure jQuery is loaded before proceeding
            if (typeof jQuery === 'undefined') {
                throw new Error('jQuery is not loaded. Cannot initialize tracking.');
            }
            await this.initializeMap();
            await this.initializeDestinationMarker(); // Waits for geocoding
            this.startCourierTracking();
        } catch (error) {
            console.error('Initialization error:', error);
            this.handleError('Error initializing tracking system: ' + error.message);
        }
    }

    async initializeMap() {
// Leaflet map initialization remains the same
        this.map = L.map('deliveryMap').setView([0, 0], 13);
        // Wrap Leaflet's event in a promise to await its load
        await new Promise((resolve) => {
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(this.map).on('load', resolve);
        });
        this.mapLoaded = true;
        console.log("Map initialized");
    }

    async initializeDestinationMarker() {
        const deliveryAddressElement = document.getElementById('deliveryAddress');
        if (!deliveryAddressElement) {
            console.warn("Delivery address element not found.");
            return; // Exit if the element doesn't exist
        }
        const address = deliveryAddressElement.dataset.address;
        if (address) {
            try {
                const coords = await this.geocodeAddress(address); // Await the promise
                if (coords && coords.lat !== 0 && coords.lng !== 0) { // Check for valid coords
                    this.destinationMarker = L.marker([coords.lat, coords.lng], {
                        icon: L.divIcon({
                            html: '<i class="mdi mdi-map-marker text-danger" style="font-size: 24px;"></i>',
                            className: 'destination-marker'
                        })
                    }).addTo(this.map);
                    this.map.setView([coords.lat, coords.lng], 13);
                    console.log("Destination marker initialized");
                } else {
                    console.warn("Could not geocode address or received default coordinates:", address);
                    this.handleError('Could not find destination address location.');
                }
            } catch (error) {
// Error already handled within geocodeAddress, but log here if needed
                console.error("Error during destination marker initialization:", error);
                // handleError is called within geocodeAddress on failure
            }
        } else {
            console.warn("No address found in data-address attribute.");
            this.handleError('Destination address not provided.');
        }
    }

    startCourierTracking() {
        const deliveryAddressElement = document.getElementById('deliveryAddress');
        if (!deliveryAddressElement) {
            console.error("Delivery address element not found for courier ID.");
            this.handleError('Configuration error: Address element missing.');
            return;
        }
        const courierId = deliveryAddressElement.dataset.courierId;
        if (!courierId) {
            this.handleError('No courier assigned');
            console.warn("No courier ID found in data-courier-id attribute.");
            return;
        }

        console.log("Starting courier tracking for ID:", courierId);
        // Initial update
        this.updateCourierLocation(courierId);
        // Set up regular updates
        this.updateInterval = setInterval(() => {
            this.updateCourierLocation(courierId);
        }, 10000); // Update every 10 seconds
    }

// No longer strictly needs async unless awaiting something internally,
// but kept for consistency. Does not return an awaited promise.
    async updateCourierLocation(courierId) {
        $.ajax({
            url: `index.php?controller=Courier&action=getLocation&courier_id=${courierId}`,
            type: 'GET',
            dataType: 'json',
            success: (data) => { // Arrow function preserves 'this'
                if (data.status === 'success' && data.data) {
                    const {latitude, longitude, timestamp} = data.data;
                    if (latitude != null && longitude != null) { // Check for null/undefined coords
                        console.log("Received courier location:", {latitude, longitude, timestamp});
                        this.updateCourierMarker(parseFloat(latitude), parseFloat(longitude));
                        // Call updateRouteAndInfo (which is also async but we don't await it here)
                        this.updateRouteAndInfo(parseFloat(latitude), parseFloat(longitude), timestamp);
                    } else {
                        console.warn("Received null or invalid coordinates for courier.");
                        this.handleError('Invalid location data received.');
                    }
                } else {
                    console.log('Waiting for courier location or received error status:', data.message || 'No details');
                    // Update status only if no marker exists yet, otherwise keep last known status
                    if (!this.courierMarker) {
                        this.handleError('Waiting for courier location...');
                    }
                }
            },
            error: (jqXHR, textStatus, errorThrown) => { // Arrow function preserves 'this'
                console.error('Error fetching courier location:', textStatus, errorThrown);
                this.handleError('Error updating courier location');
            }
        });
    }

    updateCourierMarker(lat, lng) {
        if (!this.mapLoaded)
            return; // Don't try to add marker if map isn't ready

        const latLng = L.latLng(lat, lng); // Use Leaflet's LatLng object

        if (!this.courierMarker) {
            console.log("Creating courier marker at:", latLng);
            this.courierMarker = L.marker(latLng, {
                icon: L.divIcon({
                    html: '<i class="mdi mdi-truck-fast text-primary" style="font-size: 24px;"></i>',
                    className: 'courier-marker'
                })
            }).addTo(this.map);
        } else {
            console.log("Updating courier marker position to:", latLng);
            this.courierMarker.setLatLng(latLng);
        }

// Fit bounds to show both markers if destination exists
        if (this.destinationMarker && this.courierMarker) {
            try {
                const group = new L.featureGroup([this.courierMarker, this.destinationMarker]);
                this.map.fitBounds(group.getBounds(), {padding: [50, 50], maxZoom: 16});
            } catch (e) {
                console.error("Error fitting map bounds:", e);
            }
        } else if (this.courierMarker) {
// If only courier marker exists, center on it
            this.map.setView(latLng, this.map.getZoom()); // Keep current zoom or set a default
        }
    }

// No longer strictly needs async unless awaiting something internally,
// but kept for consistency. Does not return an awaited promise.
    async updateRouteAndInfo(courierLat, courierLng, timestamp) {
        if (!this.destinationMarker || !this.mapLoaded) {
            console.log("Skipping route update (no destination or map not loaded).");
            return;
        }

        const destLatLng = this.destinationMarker.getLatLng();
        console.log("Requesting route with:", {courierLat, courierLng, destLat: destLatLng.lat, destLng: destLatLng.lng});
        // Ensure coordinates are valid numbers before making the request
        if (isNaN(courierLat) || isNaN(courierLng) || isNaN(destLatLng.lat) || isNaN(destLatLng.lng)) {
            console.error("Invalid coordinates for route calculation:", {courierLat, courierLng, destLatLng});
            this.handleError("Invalid coordinates for route.");
            return;
        }

        const osrmUrl = `https://router.project-osrm.org/route/v1/driving/` +
                `${courierLng},${courierLat};${destLatLng.lng},${destLatLng.lat}` +
                `?overview=full&geometries=geojson&steps=true`; // steps=true was in the second version
        console.log("OSRM URL:", osrmUrl); // Log the final URL

        $.ajax({
            url: osrmUrl,
            type: 'GET',
            dataType: 'json',
            success: (data) => {
                console.log("Full OSRM Response:", data); // Log the entire response
                if (data.routes && data.routes.length > 0 && data.routes[0].geometry) { // Check length and geometry explicitly
                    console.log("OSRM returned a route. Geometry:", data.routes[0].geometry);
                    this.drawRoute(data.routes[0].geometry);
                    this.updateDeliveryInfo(data.routes[0], timestamp);
                } else {
                    // Log why it failed
                    console.warn('OSRM did not return a valid route. Response code:', data.code, 'Routes array:', data.routes);
                    this.handleError('Route information currently unavailable.');
                    if (this.routeLine) {
                        this.map.removeLayer(this.routeLine);
                        this.routeLine = null;
                    }
                }
            },
            error: (jqXHR, textStatus, errorThrown) => { // Arrow function preserves 'this'
                console.error('Error fetching route from OSRM:', textStatus, errorThrown);
                this.handleError('Error calculating route');
            }
        });
    }

// Needs to return a Promise to be used with await in initializeDestinationMarker
    async geocodeAddress(address) {
        console.log("Geocoding address:", address);
        const nominatimUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`;
        // Wrap $.ajax in a Promise
        return new Promise((resolve, reject) => {
            $.ajax({
                url: nominatimUrl,
                type: 'GET',
                dataType: 'json',
                success: (data) => { // Arrow function preserves 'this' from outer scope (geocodeAddress)
                    if (data && data.length > 0) {
                        console.log("Geocoding successful:", data[0]);
                        resolve({// Resolve the promise with coordinates
                            lat: parseFloat(data[0].lat),
                            lng: parseFloat(data[0].lon)
                        });
                    } else {
                        console.warn('Geocoding successful but no results found for:', address);
                        reject(new Error('Address not found')); // Reject the promise
                    }
                },
                error: (jqXHR, textStatus, errorThrown) => { // Arrow function preserves 'this'
                    console.error('Geocoding AJAX error:', textStatus, errorThrown);
                    reject(new Error(`Geocoding failed: ${textStatus}`)); // Reject the promise
                }
            });
        }).catch(error => { // Catch promise rejection (either 'Address not found' or AJAX error)
            console.error('Geocoding error caught:', error.message);
            this.handleError(`Error finding address location: ${error.message}`);
            // Resolve with default coordinates on error to prevent breaking caller,
            // matching original behavior implicitly. Caller should check for {lat:0, lng:0}.
            return {lat: 0, lng: 0};
        });
    }

    handleError(message) {
        console.warn("Handling error:", message);
        const timeElement = document.getElementById('estimatedTime');
        const statusElement = document.getElementById('courierStatus');
        if (timeElement)
            timeElement.innerHTML = 'Not available';
        if (statusElement)
            statusElement.innerHTML = message;
        // Potentially add more robust UI feedback here
    }

    removeLoader() {
        const loadingOverlay = document.querySelector('.map-loading-overlay');
        if (loadingOverlay) {
            console.log("Removing map loader.");
            loadingOverlay.style.opacity = '0';
            // Use transitionend event for potentially smoother removal
            loadingOverlay.addEventListener('transitionend', () => {
                if (loadingOverlay.parentNode) {
                    loadingOverlay.parentNode.removeChild(loadingOverlay);
                }
            }, {once: true}); // Ensure listener runs only once
            // Fallback timeout just in case transitionend doesn't fire
            setTimeout(() => {
                if (loadingOverlay.parentNode) {
                    loadingOverlay.parentNode.removeChild(loadingOverlay);
                }
            }, 500); // Slightly longer than CSS transition (default 300ms)
        }
    }

// drawRoute, addRouteArrows, calculateAngle, updateDeliveryInfo, addRouteStyles
// remain unchanged as they don't contain fetch calls.

    /**
     * Draws a route on the map using the provided geometry
     * @param {Object} geometry - GeoJSON geometry object containing coordinates
     */
// Modified drawRoute function with proper GeoJSON formatting and styling
    // Improved drawRoute function for connecting points and showing the full route
    drawRoute(routeData) {
        console.log("➡️ drawRoute function started with:", routeData);
        if (!this.mapLoaded) {
            console.warn("Map not loaded yet, can't draw route");
            return;
        }

        // Clear existing route
        if (this.routeLine) {
            this.map.removeLayer(this.routeLine);
            this.routeLine = null;
        }

        // Clear existing arrows
        this.map.eachLayer(layer => {
            if (layer.options?.icon?.options?.className === 'route-arrow') {
                this.map.removeLayer(layer);
            }
        });

        try {
            // Process the route data to ensure proper format
            let coordinates = [];
            let geometry = null;

            // Handle different potential formats of the input data
            if (routeData.routes && routeData.routes.length > 0) {
                // Extract from standard route response
                geometry = routeData.routes[0].geometry;
                coordinates = geometry.coordinates;
            } else if (routeData.geometry) {
                // Extract directly from geometry object
                geometry = routeData.geometry;
                coordinates = geometry.coordinates;
            } else if (routeData.type === 'LineString' && routeData.coordinates) {
                // Already a GeoJSON LineString
                geometry = routeData;
                coordinates = routeData.coordinates;
            } else if (Array.isArray(routeData)) {
                // Maybe it's just an array of coordinates
                coordinates = routeData;
                geometry = {
                    type: 'LineString',
                    coordinates: coordinates
                };
            } else {
                console.warn("Unrecognized route data format:", routeData);
                return;
            }

            console.log("Processing coordinates:", coordinates.length, "points");

            // IMPORTANT: Check if coordinates are valid
            if (!coordinates || coordinates.length < 2) {
                console.error("Not enough coordinates to draw a route:", coordinates);
                return;
            }

            // Ensure coordinates are in the correct format [lng, lat]
            const validCoordinates = coordinates.filter(coord =>
                Array.isArray(coord) && coord.length >= 2 &&
                        typeof coord[0] === 'number' && typeof coord[1] === 'number'
            );

            if (validCoordinates.length < 2) {
                console.error("Not enough valid coordinates after filtering");
                return;
            }

            // Create a proper GeoJSON feature for the route
            const routeFeature = {
                type: 'Feature',
                properties: {},
                geometry: {
                    type: 'LineString',
                    coordinates: validCoordinates
                }
            };

            // Enhanced debugging - visualize the actual GeoJSON that will be used
            console.log("Route GeoJSON to be rendered:", JSON.stringify(routeFeature));

            // Create GeoJSON layer with explicit styling - with improved visibility
            this.routeLine = L.geoJSON(routeFeature, {
                style: {
                    color: '#3388ff',
                    weight: 6,
                    opacity: 0.9,
                    lineJoin: 'round',
                    lineCap: 'round',
                    className: 'route-line'
                }
            });

            // Add to map explicitly as a top layer
            this.routeLine.addTo(this.map);

            // Ensure route is visible on top of other layers
            if (this.routeLine) {
                this.routeLine.bringToFront();

                // Force a repaint by briefly changing a property and changing it back
                const originalWeight = this.routeLine.options.style.weight;
                this.routeLine.setStyle({weight: originalWeight + 0.1});
                setTimeout(() => {
                    this.routeLine.setStyle({weight: originalWeight});
                }, 50);
            }

            console.log("✅ Route line added to map");

            // Add arrows to indicate direction
            this.addRouteArrows(geometry);

            // Fit map to route bounds with padding
            const bounds = this.routeLine.getBounds();
            if (bounds && bounds.isValid()) {
                console.log("Fitting to bounds:", bounds);
                this.map.fitBounds(bounds, {padding: [60, 60], maxZoom: 16});
            } else {
                console.warn("Invalid bounds, can't fit map to route");
            }

        } catch (error) {
            console.error("❌ ERROR drawing route:", error);
        }
    }

// Improved arrow rendering with better spacing
    addRouteArrows(geometry) {
        if (!geometry || !geometry.coordinates || geometry.coordinates.length < 2 || !this.mapLoaded)
            return;

        console.log("Adding route arrows");

        const coordinates = geometry.coordinates;

        // Calculate appropriate step to show 5-10 arrows based on route length
        const totalPoints = coordinates.length;
        const desiredArrows = Math.min(10, Math.max(5, Math.floor(totalPoints / 20)));
        const step = Math.max(1, Math.floor(totalPoints / desiredArrows));

        console.log(`Adding ${desiredArrows} arrows with step ${step} for ${totalPoints} points`);

        for (let i = 0; i < coordinates.length - step; i += step) {
            const start = coordinates[i];
            const end = coordinates[i + step];

            if (start && end && start.length >= 2 && end.length >= 2) {
                // Leaflet expects [lat, lng] but GeoJSON uses [lng, lat]
                const startLatLng = L.latLng(start[1], start[0]);
                const endLatLng = L.latLng(end[1], end[0]);

                // Calculate midpoint
                const midLatLng = L.latLng(
                        (startLatLng.lat + endLatLng.lat) / 2,
                        (startLatLng.lng + endLatLng.lng) / 2
                        );

                // Calculate angle
                const angle = this.calculateAngle(startLatLng, endLatLng);

                // Create more visible arrow icon
                const arrowIcon = L.divIcon({
                    html: `<div style="
                width: 16px;
                height: 16px;
                background-color: #3388ff;
                border-radius: 50%;
                border: 2px solid white;
                box-shadow: 0 0 4px rgba(0,0,0,0.7);
                position: relative;
            ">
                <div style="
                    position: absolute;
                    top: 50%;
                    left: 100%;
                    width: 12px;
                    height: 4px;
                    background-color: #3388ff;
                    transform: translateY(-50%);
                    border-radius: 0 2px 2px 0;
                    box-shadow: 0 0 2px rgba(0,0,0,0.5);
                "></div>
                <div style="
                    position: absolute;
                    top: 50%;
                    left: 100%;
                    width: 0;
                    height: 0;
                    border-top: 6px solid transparent;
                    border-bottom: 6px solid transparent;
                    border-left: 8px solid #3388ff;
                    transform: translateY(-50%);
                    filter: drop-shadow(0 0 1px rgba(0,0,0,0.5));
                "></div>
            </div>`,
                    className: 'route-arrow',
                    iconSize: [28, 28],
                    iconAnchor: [8, 8]
                });

                // Create a marker with rotation
                const marker = L.marker(midLatLng, {
                    icon: arrowIcon,
                    interactive: false,
                    zIndexOffset: 1000 // Ensure arrows are above the route
                }).addTo(this.map);

                // Apply rotation
                const arrowElement = marker.getElement()?.querySelector('div');
                if (arrowElement) {
                    arrowElement.style.transform = `rotate(${angle}deg)`;
                }
            }
        }
    }

// Helper function to calculate angle between two points
    calculateAngle(startLatLng, endLatLng) {
        const radians = Math.atan2(
                endLatLng.lng - startLatLng.lng,
                endLatLng.lat - startLatLng.lat
                );
        return (radians * 180 / Math.PI);
    }

    updateDeliveryInfo(route, timestamp)
    {
// Update estimated time and distance
        const duration = Math.round(route.duration / 60); // In minutes
        const distance = (route.distance / 1000).toFixed(1); // In km

        const timeElement = document.getElementById('estimatedTime');
        if (timeElement) {
            timeElement.innerHTML = `${duration} min${duration !== 1 ? 's' : ''} (${distance} km)`;
        }

// Update courier status based on timestamp freshness
        const statusElement = document.getElementById('courierStatus');
        if (statusElement) {
            const now = Date.now() / 1000; // Current time in seconds
            const timeDiffMinutes = Math.round((now - timestamp) / 60);
            let status = 'Status unavailable';
            if (timestamp) { // Ensure timestamp is valid
                if (timeDiffMinutes < 2) { // Consider active if updated within last 2 mins
                    status = `Active - On route`;
                } else if (timeDiffMinutes < 60) {
                    status = `Last update ${timeDiffMinutes} min${timeDiffMinutes !== 1 ? 's' : ''} ago`;
                } else {
                    status = `Last update ${Math.round(timeDiffMinutes / 60)} hour${Math.round(timeDiffMinutes / 60) !== 1 ? 's' : ''} ago`;
                }
            }
            statusElement.innerHTML = status;
        }
    }
}

// --- Initialization ---
document.addEventListener('DOMContentLoaded', () => {
    const mapContainer = document.getElementById('deliveryMap');
    if (mapContainer) {
// Optional: Add loading indicator dynamically
        if (!document.querySelector('.map-loading-overlay')) {
            const loader = document.createElement('div');
            loader.className = 'map-loading-overlay';
            loader.innerHTML = 'Loading Map...'; // Or add a spinner SVG/icon
            // Insert loader inside the map container or relatively positioned parent
            mapContainer.style.position = 'relative'; // Ensure overlay is positioned correctly
            mapContainer.appendChild(loader);
        }

        console.log("DOM loaded, initializing CourierTracking.");
        const tracking = new CourierTracking();
        // Use .then() and .catch() on the init() promise
        tracking.init()
                .then(() => {
                    console.log("CourierTracking initialization successful.");
                    tracking.removeLoader();
                })
                .catch(error => {
                    console.error('Error during CourierTracking initialization:', error);
                    // Ensure loader is removed even on error
                    tracking.removeLoader();
                    // Display error to user if needed (handleError might already do this)
                    if (!document.getElementById('courierStatus')) { // If no specific status element exists
                        alert("Failed to initialize the tracking map. Please try again later.");
                    }
                });
    } else {
        console.error("Map container 'deliveryMap' not found.");
    }
});