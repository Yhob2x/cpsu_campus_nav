<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>CPSU Navigation - Google Maps Style</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
            -webkit-user-select: none;
            user-select: none;
        }

        html, body {
            width: 100%;
            height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #fff;
            overflow: hidden;
            position: fixed;
            top: 0;
            left: 0;
        }

        /* Map Container */
        #map {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1;
        }

        /* Header Search Bar */
        .search-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            background: white;
            padding: 12px 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
            display: flex;
            gap: 12px;
            align-items: center;
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from { transform: translateY(-100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .search-container {
            flex: 1;
            display: flex;
            align-items: center;
            background: #f5f5f5;
            border-radius: 24px;
            padding: 10px 16px;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .search-container.active {
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            flex: 1;
        }

        .search-icon {
            color: #999;
            font-size: 16px;
        }

        .search-input {
            flex: 1;
            border: none;
            background: transparent;
            font-size: 16px;
            color: #333;
            outline: none;
        }

        .search-input::placeholder {
            color: #999;
        }

        .search-input:focus {
            outline: none;
        }

        .account-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e8e8e8;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #333;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .account-btn:active {
            transform: scale(0.95);
            background: #d0d0d0;
        }

        /* Bottom Sheet Navigation */
        .bottom-sheet {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 200;
            background: white;
            border-radius: 20px 20px 0 0;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.15);
            max-height: 60vh;
            overflow-y: auto;
            transform: translateY(100%);
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            animation: sheetSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes sheetSlideIn {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }

        .bottom-sheet.show {
            transform: translateY(0);
        }

        .bottom-sheet.hide {
            transform: translateY(100%);
            animation: sheetSlideOut 0.3s ease-in;
        }

        @keyframes sheetSlideOut {
            from { transform: translateY(0); }
            to { transform: translateY(100%); }
        }

        .sheet-handle {
            width: 40px;
            height: 4px;
            background: #ddd;
            border-radius: 2px;
            margin: 12px auto;
            cursor: grab;
        }

        .sheet-handle:active {
            cursor: grabbing;
        }

        .sheet-content {
            padding: 0 16px 24px;
        }

        .destination-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
            gap: 12px;
        }

        .destination-info h2 {
            font-size: 20px;
            font-weight: 600;
            color: #212121;
            margin-bottom: 4px;
        }

        .destination-info p {
            font-size: 13px;
            color: #666;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .close-sheet-btn {
            background: none;
            border: none;
            font-size: 24px;
            color: #666;
            cursor: pointer;
            padding: 8px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .close-sheet-btn:active {
            color: #000;
        }

        /* Route Details */
        .route-details {
            background: #f8f8f8;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .route-option {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .route-option:last-child {
            margin-bottom: 0;
        }

        .route-option:active {
            background: rgba(0, 0, 0, 0.05);
        }

        .route-icon {
            font-size: 24px;
            color: #3b82f6;
            width: 40px;
            text-align: center;
        }

        .route-info {
            flex: 1;
        }

        .route-info strong {
            display: block;
            font-size: 16px;
            color: #212121;
            margin-bottom: 2px;
        }

        .route-info small {
            font-size: 13px;
            color: #999;
        }

        .start-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            animation: slideUp 0.4s ease-out;
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .start-btn:active {
            transform: scale(0.98);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
        }

        /* Floating Action Buttons */
        .fab-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 150;
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: flex-end;
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .fab {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: white;
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #3b82f6;
            transition: all 0.3s ease;
        }

        .fab:active {
            transform: scale(0.9);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.4);
        }

        .fab.primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            width: 64px;
            height: 64px;
            font-size: 28px;
        }

        .fab-label {
            font-size: 12px;
            color: #666;
            white-space: nowrap;
            margin-right: 12px;
            opacity: 0;
            transform: translateX(10px);
            transition: all 0.2s ease;
            pointer-events: none;
        }

        .fab:hover .fab-label {
            opacity: 1;
            transform: translateX(0);
        }

        /* Navigation Active View */
        .nav-active {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 300;
            background: white;
            display: none;
            flex-direction: column;
            animation: fadeIn 0.3s ease-out;
        }

        .nav-active.show {
            display: flex;
        }

        .nav-header {
            padding: 16px;
            background: white;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-header-back {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            padding: 8px;
        }

        .nav-header h3 {
            font-size: 18px;
            font-weight: 600;
            flex: 1;
        }

        .nav-content {
            flex: 1;
            overflow-y: auto;
            padding: 12px 0;
        }

        .nav-step {
            padding: 16px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }

        .nav-step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #3b82f6;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            flex-shrink: 0;
        }

        .nav-step.completed .nav-step-number {
            background: #22c55e;
        }

        .nav-step-info {
            flex: 1;
        }

        .nav-step-info strong {
            display: block;
            font-size: 16px;
            color: #212121;
            margin-bottom: 4px;
        }

        .nav-step-info small {
            font-size: 13px;
            color: #999;
            display: block;
            margin-bottom: 4px;
        }

        .nav-step-distance {
            font-size: 12px;
            color: #3b82f6;
            font-weight: 600;
        }

        .progress-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: #3b82f6;
            z-index: 350;
        }

        /* Animated Walking Marker */
        .walking-marker {
            width: 24px;
            height: 24px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 0 3px #3b82f6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            font-weight: bold;
            animation: pulse 1.5s ease-in-out infinite;
            position: relative;
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 0 3px #3b82f6, 0 0 0 6px rgba(59, 130, 246, 0.3);
                transform: scale(1);
            }
            50% {
                box-shadow: 0 0 0 3px #3b82f6, 0 0 0 8px rgba(59, 130, 246, 0.1);
                transform: scale(1.05);
            }
        }

        .walking-indicator {
            display: inline-block;
            width: 6px;
            height: 6px;
            background: white;
            border-radius: 50%;
            animation: walk 0.8s ease-in-out infinite;
        }

        @keyframes walk {
            0%, 100% { transform: translateX(-4px); }
            50% { transform: translateX(4px); }
        }

        /* Route Animation */
        .route-line {
            stroke-dasharray: 10;
            stroke-dashoffset: 10;
            animation: drawRoute 2s ease-in-out forwards;
        }

        @keyframes drawRoute {
            to { stroke-dashoffset: 0; }
        }

        /* Stats Badge */
        .stats-badge {
            position: fixed;
            top: 70px;
            left: 16px;
            background: white;
            padding: 12px 16px;
            border-radius: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            font-size: 13px;
            font-weight: 500;
            color: #212121;
            z-index: 100;
            display: none;
            gap: 12px;
            align-items: center;
            animation: slideUp 0.3s ease-out;
        }

        .stats-badge.show {
            display: flex;
        }

        .stats-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22c55e;
            animation: blink 1s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Loading */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 400;
            backdrop-filter: blur(2px);
        }

        .spinner {
            width: 48px;
            height: 48px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 640px) {
            .search-header {
                padding: 8px 12px;
            }

            .fab-container {
                bottom: 16px;
                right: 16px;
                gap: 8px;
            }

            .bottom-sheet {
                border-radius: 16px 16px 0 0;
                max-height: 70vh;
            }

            .sheet-content {
                padding: 0 12px 16px;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #ddd;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #999;
        }

        /* Haptic Feedback */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <!-- Map Container -->
    <div id="map"></div>

    <!-- Search Header -->
    <div class="search-header">
        <div class="search-container" id="searchContainer">
            <i class="search-icon fas fa-search"></i>
            <input
                type="text"
                class="search-input"
                id="searchInput"
                placeholder="Search offices or buildings..."
                autocomplete="off"
            >
        </div>
        <button class="account-btn" onclick="alert('Account')">
            <i class="fas fa-user-circle"></i>
        </button>
    </div>

    <!-- Stats Badge -->
    <div class="stats-badge" id="statsBadge">
        <div class="stats-dot"></div>
        <span id="statsText">Live</span>
    </div>

    <!-- Bottom Sheet Navigation -->
    <div class="bottom-sheet" id="bottomSheet">
        <div class="sheet-handle"></div>
        <div class="sheet-content">
            <div class="destination-header">
                <div class="destination-info">
                    <h2 id="destName">-</h2>
                    <p><i class="fas fa-building" style="font-size: 12px;"></i> <span id="destBuilding">-</span></p>
                    <p><i class="fas fa-door-open" style="font-size: 12px;"></i> <span id="destRoom">-</span></p>
                </div>
                <button class="close-sheet-btn" onclick="closeBottomSheet()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="route-details">
                <div class="route-option" onclick="selectRoute('walking')">
                    <div class="route-icon">
                        <i class="fas fa-walking"></i>
                    </div>
                    <div class="route-info">
                        <strong id="walkDist">5 min walk</strong>
                        <small id="walkDis">Loading...</small>
                    </div>
                </div>
            </div>

            <button class="start-btn" onclick="startNavigation()">
                <i class="fas fa-location-arrow" style="margin-right: 8px;"></i> Start Navigation
            </button>
        </div>
    </div>

    <!-- Floating Action Buttons -->
    <div class="fab-container">
        <button class="fab" onclick="getCurrentLocation()" title="Current Location">
            <i class="fas fa-location-dot"></i>
        </button>
        <button class="fab" onclick="toggleLayers()" title="Layers">
            <i class="fas fa-layer-group"></i>
        </button>
        <button class="fab primary" onclick="showNearestOffice()" title="Nearby">
            <i class="fas fa-compass"></i>
        </button>
    </div>

    <!-- Navigation Active View -->
    <div class="nav-active" id="navActive">
        <div class="nav-header">
            <button class="nav-header-back" onclick="stopNavigation()">
                <i class="fas fa-arrow-left"></i>
            </button>
            <h3>Directions</h3>
            <div style="width: 40px;"></div>
        </div>
        <div class="nav-content" id="navContent"></div>
        <div class="progress-bar" style="width: 0%;" id="progressBar"></div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay" style="display: none;">
        <div class="spinner"></div>
    </div>

    <script>
        // Global Variables
        let map;
        let userMarker = null;
        let userAccuracyCircle = null;
        let routePolyline = null;
        let markers = {};
        let officesData = [];
        let footwalkPaths = [];
        let pathGraph = {};
        let selectedDestination = null;
        let currentRoute = null;
        let isNavigating = false;
        let watchId = null;
        let lastUserPosition = null;
        let currentRoutePoints = [];
        let navigationSteps = [];
        let currentStepIndex = 0;

        // Initialize Map
        function initMap() {
            showLoading(true);
            map = L.map('map').setView([9.853, 122.890], 18);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap',
                maxZoom: 19,
            }).addTo(map);

            loadOffices();
            loadFootwalks();

            window.addEventListener('resize', () => setTimeout(() => map.invalidateSize(), 100));
        }

        // Load Offices
        async function loadOffices() {
            try {
                const response = await fetch('/api/offices');
                const data = await response.json();
                if (data.success) {
                    officesData = data.data;
                    officesData.forEach(office => addOfficeMarker(office));
                }
            } catch (error) {
                console.error('Error loading offices:', error);
            }
        }

        // Add Office Marker
        function addOfficeMarker(office) {
            if (!office.lat || !office.lng) return;

            let markerColor = office.category === 'Administrative' ? '#ef4444' :
                            office.category === 'Academic' ? '#3b82f6' : '#10b981';

            const icon = L.divIcon({
                html: `<div style="background-color: ${markerColor}; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: bold;"></div>`,
                iconSize: [24, 24]
            });

            const marker = L.marker([office.lat, office.lng], { icon }).addTo(map);
            marker.on('click', () => selectOffice(office));
            markers[office.office_id] = marker;
        }

        // Select Office
        function selectOffice(office) {
            selectedDestination = office;
            showBottomSheet(office);
            map.setView([office.lat, office.lng], 18);
        }

        // Show Bottom Sheet
        function showBottomSheet(office) {
            document.getElementById('destName').textContent = office.name;
            document.getElementById('destBuilding').textContent = office.building || 'Main Building';
            document.getElementById('destRoom').textContent = office.room_number || 'Ground Floor';

            // Calculate distance
            if (lastUserPosition) {
                const dist = calculateDistance(
                    lastUserPosition.lat, lastUserPosition.lng,
                    office.lat, office.lng
                );
                const walkTime = Math.round(dist / 1.4) / 60; // Assuming 1.4 m/s walking speed
                document.getElementById('walkDist').textContent = `${Math.round(walkTime)} min walk`;
                document.getElementById('walkDis').textContent = `${Math.round(dist)}m`;
            }

            document.getElementById('bottomSheet').classList.add('show');
        }

        // Close Bottom Sheet
        function closeBottomSheet() {
            document.getElementById('bottomSheet').classList.remove('show');
            selectedDestination = null;
        }

        // Calculate Distance
        function calculateDistance(lat1, lng1, lat2, lng2) {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLng = (lng2 - lng1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLng / 2) * Math.sin(dLng / 2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        // Load Footwalks
        async function loadFootwalks() {
            try {
                const response = await fetch('/api/footwalks');
                const data = await response.json();
                if (data.success && data.data) {
                    footwalkPaths = data.data;
                    footwalkPaths.forEach(path => drawFootwalk(path));
                    buildPathGraph();
                }
                showLoading(false);
            } catch (error) {
                console.error('Error loading footwalks:', error);
                showLoading(false);
            }
        }

        // Draw Footwalk
        function drawFootwalk(path) {
            try {
                const coordinates = JSON.parse(path.coordinates);
                const latlngs = coordinates.map(c => [c[0], c[1]]);
                L.polyline(latlngs, {
                    color: path.color || '#3b82f6',
                    weight: 4,
                    opacity: 0.6
                }).addTo(map);
            } catch (e) {}
        }

        // Build Path Graph (simplified)
        function buildPathGraph() {
            // Build graph for pathfinding
            pathGraph = {};
            let nodeId = 0;
            const pointToId = new Map();

            footwalkPaths.forEach(path => {
                const coords = JSON.parse(path.coordinates);
                for (let i = 0; i < coords.length; i++) {
                    const key = `${coords[i][0].toFixed(6)},${coords[i][1].toFixed(6)}`;
                    if (!pointToId.has(key)) {
                        pointToId.set(key, nodeId++);
                        pathGraph[pointToId.get(key)] = {
                            lat: coords[i][0],
                            lng: coords[i][1],
                            edges: []
                        };
                    }
                }
            });

            // Connect points
            footwalkPaths.forEach(path => {
                const coords = JSON.parse(path.coordinates);
                for (let i = 0; i < coords.length - 1; i++) {
                    const key1 = `${coords[i][0].toFixed(6)},${coords[i][1].toFixed(6)}`;
                    const key2 = `${coords[i + 1][0].toFixed(6)},${coords[i + 1][1].toFixed(6)}`;
                    const id1 = pointToId.get(key1);
                    const id2 = pointToId.get(key2);
                    if (id1 !== undefined && id2 !== undefined) {
                        const dist = calculateDistance(coords[i][0], coords[i][1], coords[i + 1][0], coords[i + 1][1]);
                        if (!pathGraph[id1].edges.some(e => e.to === id2)) {
                            pathGraph[id1].edges.push({ to: id2, dist });
                            pathGraph[id2].edges.push({ to: id1, dist });
                        }
                    }
                }
            });
        }

        // Find Nearest Node
        function findNearestNode(lat, lng) {
            let nearest = null;
            let minDist = Infinity;

            Object.keys(pathGraph).forEach(id => {
                const node = pathGraph[id];
                const dist = calculateDistance(lat, lng, node.lat, node.lng);
                if (dist < minDist) {
                    minDist = dist;
                    nearest = { id: parseInt(id), node, dist: minDist };
                }
            });

            return nearest;
        }

        // Find Shortest Path (Dijkstra)
        function findShortestPath(startLat, startLng, endLat, endLng) {
            const startNode = findNearestNode(startLat, startLng);
            const endNode = findNearestNode(endLat, endLng);

            if (!startNode || !endNode || Object.keys(pathGraph).length === 0) {
                return [[startLat, startLng], [endLat, endLng]];
            }

            const distances = {};
            const previous = {};
            const unvisited = new Set();

            Object.keys(pathGraph).forEach(id => {
                distances[id] = Infinity;
                unvisited.add(parseInt(id));
            });
            distances[startNode.id] = 0;

            while (unvisited.size > 0) {
                let current = null;
                let minDist = Infinity;

                unvisited.forEach(id => {
                    if (distances[id] < minDist) {
                        minDist = distances[id];
                        current = id;
                    }
                });

                if (!current) break;
                unvisited.delete(current);

                if (pathGraph[current] && pathGraph[current].edges) {
                    pathGraph[current].edges.forEach(edge => {
                        if (unvisited.has(edge.to)) {
                            const alt = distances[current] + edge.dist;
                            if (alt < distances[edge.to]) {
                                distances[edge.to] = alt;
                                previous[edge.to] = current;
                            }
                        }
                    });
                }
            }

            const path = [];
            let current = endNode.id;

            while (current !== undefined && current !== startNode.id) {
                path.unshift(current);
                current = previous[current];
            }
            path.unshift(startNode.id);

            const routePoints = [];
            routePoints.push([startLat, startLng]);

            path.forEach(id => {
                if (pathGraph[id]) {
                    routePoints.push([pathGraph[id].lat, pathGraph[id].lng]);
                }
            });

            routePoints.push([endLat, endLng]);

            // Remove duplicates
            const unique = [];
            routePoints.forEach(p => {
                if (unique.length === 0 || unique[unique.length - 1][0] !== p[0] || unique[unique.length - 1][1] !== p[1]) {
                    unique.push(p);
                }
            });

            return unique;
        }

        // Start Navigation
        function startNavigation() {
            if (!selectedDestination) return;

            isNavigating = true;
            document.getElementById('bottomSheet').classList.remove('show');
            document.getElementById('navActive').classList.add('show');
            document.getElementById('statsBadge').classList.add('show');

            getCurrentLocation();
        }

        // Get Current Location
        function getCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const { latitude, longitude, accuracy } = position.coords;
                        updateUserLocation(latitude, longitude, accuracy);

                        if (isNavigating && selectedDestination) {
                            calculateRoute(latitude, longitude, selectedDestination.lat, selectedDestination.lng);
                            startTracking();
                        }
                    },
                    (error) => {
                        console.error('Geolocation error:', error);
                        alert('Unable to get your location. Please enable location services.');
                    }
                );
            }
        }

        // Update User Location
        function updateUserLocation(lat, lng, accuracy = null) {
            lastUserPosition = { lat, lng };

            if (userMarker) map.removeLayer(userMarker);
            if (userAccuracyCircle) map.removeLayer(userAccuracyCircle);

            const icon = L.divIcon({
                html: '<div class="walking-marker"><span class="walking-indicator">●</span></div>',
                iconSize: [24, 24]
            });

            userMarker = L.marker([lat, lng], { icon }).addTo(map);

            if (accuracy && accuracy < 100) {
                userAccuracyCircle = L.circle([lat, lng], {
                    radius: accuracy,
                    color: '#3b82f6',
                    weight: 1,
                    opacity: 0.3,
                    fillColor: '#3b82f6',
                    fillOpacity: 0.05
                }).addTo(map);
            }

            if (!isNavigating) {
                map.setView([lat, lng], 18);
            }
        }

        // Calculate Route
        function calculateRoute(startLat, startLng, endLat, endLng) {
            const routePoints = findShortestPath(startLat, startLng, endLat, endLng);
            currentRoutePoints = routePoints;

            if (routePolyline) map.removeLayer(routePolyline);

            routePolyline = L.polyline(routePoints, {
                color: '#3b82f6',
                weight: 5,
                opacity: 0.8,
                dashArray: '10, 5'
            }).addTo(map);

            generateNavigationSteps(routePoints);
            map.fitBounds(routePolyline.getBounds().pad(0.1));
        }

        // Generate Navigation Steps
        function generateNavigationSteps(routePoints) {
            navigationSteps = [];
            let totalDistance = 0;

            for (let i = 0; i < routePoints.length - 1; i++) {
                const dist = calculateDistance(
                    routePoints[i][0], routePoints[i][1],
                    routePoints[i + 1][0], routePoints[i + 1][1]
                );
                totalDistance += dist;

                navigationSteps.push({
                    step: i + 1,
                    direction: getDirection(routePoints[i], routePoints[i + 1]),
                    distance: dist,
                    totalDistance: totalDistance,
                    completed: false
                });
            }

            renderNavigationSteps();
        }

        // Get Direction
        function getDirection(from, to) {
            const dLat = to[0] - from[0];
            const dLng = to[1] - from[1];
            const angle = Math.atan2(dLng, dLat) * 180 / Math.PI;

            if (angle > -45 && angle < 45) return 'North';
            if (angle >= 45 && angle < 135) return 'East';
            if (angle >= 135 || angle < -135) return 'South';
            return 'West';
        }

        // Render Navigation Steps
        function renderNavigationSteps() {
            const navContent = document.getElementById('navContent');
            navContent.innerHTML = navigationSteps.map((step, idx) => `
                <div class="nav-step ${step.completed ? 'completed' : ''}" id="step-${idx}">
                    <div class="nav-step-number">${step.step}</div>
                    <div class="nav-step-info">
                        <strong>Head ${step.direction}</strong>
                        <small>${step.distance > 1000 ? (step.distance / 1000).toFixed(1) + ' km' : Math.round(step.distance) + ' m'}</small>
                        <div class="nav-step-distance">Total: ${(step.totalDistance / 1000).toFixed(2)} km</div>
                    </div>
                </div>
            `).join('');
        }

        // Start Tracking
        function startTracking() {
            if (watchId) navigator.geolocation.clearWatch(watchId);

            watchId = navigator.geolocation.watchPosition(
                (position) => {
                    const { latitude, longitude } = position.coords;
                    updateUserLocation(latitude, longitude);

                    if (isNavigating && currentRoutePoints.length > 0) {
                        let progress = 0;
                        let minDist = Infinity;

                        for (let i = 0; i < currentRoutePoints.length - 1; i++) {
                            const dist = calculateDistance(
                                latitude, longitude,
                                currentRoutePoints[i][0], currentRoutePoints[i][1]
                            );
                            if (dist < minDist) {
                                minDist = dist;
                                progress = i / currentRoutePoints.length;
                            }
                        }

                        document.getElementById('progressBar').style.width = (progress * 100) + '%';
                        currentStepIndex = Math.floor(progress * navigationSteps.length);
                        updateNavigationSteps();

                        // Check if arrived
                        const distToDest = calculateDistance(
                            latitude, longitude,
                            selectedDestination.lat, selectedDestination.lng
                        );

                        if (distToDest < 20) {
                            arrival();
                        }
                    }
                },
                (error) => console.error('Tracking error:', error),
                { enableHighAccuracy: true, maximumAge: 2000, timeout: 10000 }
            );
        }

        // Update Navigation Steps
        function updateNavigationSteps() {
            navigationSteps.forEach((step, idx) => {
                const stepEl = document.getElementById(`step-${idx}`);
                if (idx < currentStepIndex) {
                    step.completed = true;
                    stepEl.classList.add('completed');
                } else {
                    stepEl.classList.remove('completed');
                }
            });
        }

        // Arrival
        function arrival() {
            stopNavigation();
            alert('🎉 You have arrived at ' + selectedDestination.name);
        }

        // Stop Navigation
        function stopNavigation() {
            isNavigating = false;
            if (watchId) navigator.geolocation.clearWatch(watchId);
            document.getElementById('navActive').classList.remove('show');
            document.getElementById('statsBadge').classList.remove('show');
            if (routePolyline) map.removeLayer(routePolyline);
            currentStepIndex = 0;
        }

        // Search
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            if (query.length < 2) return;

            const results = officesData.filter(o =>
                o.name.toLowerCase().includes(query) ||
                o.building.toLowerCase().includes(query)
            );

            if (results.length > 0) {
                selectOffice(results[0]);
            }
        });

        // Get Current Location Button
        function getCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const { latitude, longitude } = position.coords;
                        updateUserLocation(latitude, longitude);
                        map.setView([latitude, longitude], 18);
                    },
                    (error) => alert('Unable to get location')
                );
            }
        }

        // Show Nearest Office
        function showNearestOffice() {
            if (!lastUserPosition) {
                getCurrentLocation();
                return;
            }

            let nearest = null;
            let minDist = Infinity;

            officesData.forEach(office => {
                const dist = calculateDistance(
                    lastUserPosition.lat, lastUserPosition.lng,
                    office.lat, office.lng
                );
                if (dist < minDist) {
                    minDist = dist;
                    nearest = office;
                }
            });

            if (nearest) {
                selectOffice(nearest);
            }
        }

        // Toggle Layers
        function toggleLayers() {
            alert('Layers feature coming soon!');
        }

        // Show Loading
        function showLoading(show) {
            document.getElementById('loadingOverlay').style.display = show ? 'flex' : 'none';
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', initMap);
    </script>
</body>
</html>
