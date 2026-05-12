<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>CPSU Map Navigator - Live GPS Navigation</title>
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            -webkit-tap-highlight-color: transparent;
        }

        :root {
            --primary: #0284c7;
            --primary-light: #38bdf8;
            --accent: #16a34a;
            --accent-light: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #06b6d4;
        }
        
        body {
            overflow-x: hidden;
            position: fixed;
            width: 100%;
            height: 100%;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            background: #f8fafc;
        }
        
        /* Map Container */
        #map {
            height: 100%;
            width: 100%;
            border-radius: 0;
            z-index: 1;
        }

        /* Header Styling */
        .modern-header {
            background: linear-gradient(135deg, var(--primary) 0%, #0369a1 100%);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            padding: 12px 16px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 50;
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 60px;
        }

        .header-content {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }

        .header-logo {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.2rem;
            color: var(--primary);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .header-title {
            color: white;
            font-weight: 700;
            font-size: 1rem;
        }

        .live-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            margin-left: auto;
            margin-right: 12px;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: #22c55e;
            border-radius: 50%;
            animation: pulse-dot 1.5s ease-in-out infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        /* Control Buttons */
        .header-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .icon-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
            backdrop-filter: blur(10px);
        }

        .icon-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }

        .icon-btn:active {
            transform: scale(0.95);
        }

        /* Search Bar */
        .search-container {
            position: fixed;
            top: 70px;
            left: 0;
            right: 0;
            z-index: 40;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 12px 16px;
        }

        .search-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-icon {
            position: absolute;
            left: 12px;
            color: #94a3b8;
            pointer-events: none;
        }

        #searchInput {
            width: 100%;
            padding: 10px 12px 10px 38px;
            border: 2px solid #e2e8f0;
            border-radius: 24px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        #searchInput:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
        }

        #searchInput::placeholder {
            color: #cbd5e1;
        }

        /* Control Buttons Bar */
        .controls-bar {
            position: fixed;
            top: 130px;
            left: 0;
            right: 0;
            z-index: 40;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 16px;
        }

        .button-group {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .control-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 16px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .control-btn-primary {
            background: linear-gradient(135deg, var(--accent) 0%, #15803d 100%);
            color: white;
        }

        .control-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
        }

        .control-btn-secondary {
            background: #e0f2fe;
            color: var(--primary);
        }

        .control-btn-secondary:hover {
            background: #bae6fd;
        }

        .control-btn-neutral {
            background: #f1f5f9;
            color: #475569;
        }

        .control-btn-neutral:hover {
            background: #e2e8f0;
        }

        /* Legend */
        .legend-container {
            position: fixed;
            top: 200px;
            left: 0;
            right: 0;
            z-index: 40;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 16px;
            overflow-x: auto;
        }

        .legend {
            display: flex;
            gap: 16px;
            min-width: min-content;
            padding: 4px 0;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8rem;
            color: #475569;
            flex-shrink: 0;
        }

        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* Info Panel */
        .info-panel {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            padding: 20px;
            max-width: 320px;
            z-index: 200;
            transform: translateY(120%);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            border-left: 4px solid var(--primary);
        }

        .info-panel.show {
            transform: translateY(0);
            opacity: 1;
        }

        .info-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .info-panel-title {
            font-weight: 700;
            font-size: 1rem;
            color: #1e293b;
        }

        .close-btn {
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .close-btn:hover {
            color: #64748b;
        }

        .info-detail {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-badge {
            display: inline-block;
            background: #dbeafe;
            color: var(--primary);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 8px;
        }

        .navigate-btn {
            width: 100%;
            padding: 12px 16px;
            background: linear-gradient(135deg, var(--primary) 0%, #0369a1 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            margin-top: 12px;
            transition: all 0.3s ease;
        }

        .navigate-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.3);
        }

        /* Navigation Instructions */
        .nav-instructions {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            padding: 16px;
            max-width: 380px;
            z-index: 200;
            transform: translateY(120%);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            border-top: 4px solid var(--accent);
            max-height: 45vh;
            overflow-y: auto;
        }

        .nav-instructions.show {
            transform: translateY(0);
            opacity: 1;
        }

        .nav-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
        }

        .nav-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            color: #1e293b;
        }

        .nav-distance {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 12px;
            padding: 8px 12px;
            background: #eff6ff;
            border-radius: 8px;
            border-left: 3px solid var(--info);
        }

        .nav-steps {
            font-size: 0.85rem;
            color: #475569;
            space-y: 8px;
        }

        .nav-step {
            padding: 8px 0;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            border-bottom: 1px solid #e2e8f0;
        }

        .nav-step:last-child {
            border-bottom: none;
        }

        .nav-icon {
            min-width: 20px;
            text-align: center;
            font-size: 0.9rem;
        }

        /* GPS Indicator */
        .gps-indicator {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 56px;
            height: 56px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            z-index: 180;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
            font-size: 1.2rem;
            color: #475569;
        }

        .gps-indicator:hover {
            transform: scale(1.05);
        }

        .gps-indicator.active {
            background: linear-gradient(135deg, var(--accent) 0%, #15803d 100%);
            color: white;
            border-color: var(--accent);
            animation: pulse-button 2s ease-in-out infinite;
        }

        @keyframes pulse-button {
            0%, 100% { box-shadow: 0 4px 16px rgba(22, 163, 74, 0.3); }
            50% { box-shadow: 0 4px 20px rgba(22, 163, 74, 0.6); }
        }

        /* Loading */
        .loading {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 300;
        }

        .loading-spinner {
            background: white;
            padding: 32px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #e2e8f0;
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 16px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Toast Notifications */
        .toast {
            position: fixed;
            bottom: 100px;
            left: 16px;
            right: 16px;
            padding: 14px 16px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
            font-size: 0.9rem;
            z-index: 250;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .toast-success {
            background: #f0fdf4;
            color: #166534;
            border-left: 4px solid var(--accent);
        }

        .toast-error {
            background: #fef2f2;
            color: #991b1b;
            border-left: 4px solid var(--danger);
        }

        .toast-info {
            background: #f0f9ff;
            color: #0c4a6e;
            border-left: 4px solid var(--info);
        }

        /* Responsive Design */
        @media (max-width: 640px) {
            .search-container {
                top: 60px;
            }

            .controls-bar {
                top: 120px;
            }

            .legend-container {
                top: 190px;
            }

            #map {
                margin-top: 0;
            }

            .info-panel {
                bottom: 12px;
                right: 12px;
                left: 12px;
                max-width: none;
            }

            .nav-instructions {
                bottom: 12px;
                left: 12px;
                right: 12px;
                max-width: none;
                max-height: 50vh;
            }

            .button-group {
                grid-template-columns: repeat(3, 1fr);
            }

            .control-btn {
                padding: 10px 8px;
                font-size: 0.75rem;
            }

            .gps-indicator {
                bottom: 12px;
                right: 12px;
                width: 48px;
                height: 48px;
                font-size: 1rem;
            }

            .legend {
                gap: 12px;
            }

            .legend-item {
                font-size: 0.75rem;
            }
        }

        @media (min-width: 641px) {
            .map-container {
                margin-top: 240px;
            }
        }

        /* Custom Markers */
        .user-marker {
            background: linear-gradient(135deg, var(--accent-light) 0%, #16a34a 100%);
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 0 16px rgba(22, 163, 74, 0.5);
            animation: pulse-marker 2s ease-in-out infinite;
        }

        @keyframes pulse-marker {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Safe Area */
        @supports (padding-bottom: env(safe-area-inset-bottom)) {
            .info-panel, .nav-instructions {
                margin-bottom: env(safe-area-inset-bottom);
            }
        }
    </style>
</head>
<body>
    <!-- Modern Header -->
    <header class="modern-header">
        <div class="header-content">
            <div class="header-logo">🗺️</div>
            <h1 class="header-title">CPSU Navigator</h1>
        </div>
        <div class="live-indicator hidden" id="liveBadge">
            <div class="live-dot"></div>
            <span>LIVE</span>
        </div>
        <div class="header-actions">
            <a href="{{ url('/directory') }}" class="icon-btn" title="Office Directory">
                <i class="fas fa-list"></i>
            </a>
            <a href="{{ url('/login') }}" class="icon-btn" title="Admin">
                <i class="fas fa-user-shield"></i>
            </a>
        </div>
    </header>

    <!-- Search Bar -->
    <div class="search-container">
        <div class="search-input-wrapper">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="searchInput" placeholder="Search offices, buildings...">
        </div>
    </div>

    <!-- Control Buttons -->
    <div class="controls-bar">
        <div class="button-group">
            <button onclick="centerToUser()" class="control-btn control-btn-primary" title="My Location">
                <i class="fas fa-location-dot"></i> <span>My Location</span>
            </button>
            <button onclick="findNearestOffice()" class="control-btn control-btn-secondary" title="Find Nearest">
                <i class="fas fa-building"></i> <span>Nearest</span>
            </button>
            <button onclick="resetMap()" class="control-btn control-btn-neutral" title="Reset">
                <i class="fas fa-home"></i> <span>Reset</span>
            </button>
        </div>
    </div>

    <!-- Legend -->
    <div class="legend-container">
        <div class="legend">
            <div class="legend-item">
                <div class="legend-dot" style="background-color: #ef4444;"></div>
                <span>Admin</span>
            </div>
            <div class="legend-item">
                <div class="legend-dot" style="background-color: #3b82f6;"></div>
                <span>Academic</span>
            </div>
            <div class="legend-item">
                <div class="legend-dot" style="background-color: #22c55e;"></div>
                <span>Facility</span>
            </div>
            <div class="legend-item">
                <i class="fas fa-road" style="color: #3b82f6; font-size: 0.9rem;"></i>
                <span>Footwalk</span>
            </div>
            <div class="legend-item">
                <i class="fas fa-route" style="color: #22c55e; font-size: 0.9rem;"></i>
                <span>Your Path</span>
            </div>
            <div class="legend-item">
                <div class="legend-dot" style="background-color: #f59e0b;"></div>
                <span>Connection</span>
            </div>
        </div>
    </div>

    <!-- Map -->
    <div id="map"></div>

    <!-- GPS Indicator -->
    <div id="gpsIndicator" class="gps-indicator" onclick="toggleLiveTracking()" title="Toggle Live Tracking">
        <i class="fas fa-location-arrow"></i>
    </div>

    <!-- Info Panel -->
    <div id="infoPanel" class="info-panel">
        <div class="info-panel-header">
            <h3 id="officeName" class="info-panel-title"></h3>
            <button onclick="closePanel()" class="close-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="officeDetails" class="space-y-2"></div>
        <button onclick="navigateToOffice()" class="navigate-btn">
            <i class="fas fa-directions mr-2"></i> Navigate to this Office
        </button>
    </div>

    <!-- Navigation Instructions -->
    <div id="navInstructions" class="nav-instructions">
        <div class="nav-header">
            <div class="nav-title">
                <i class="fas fa-location-arrow" style="color: var(--accent);"></i>
                <span>Directions</span>
            </div>
            <button onclick="stopNavigation()" class="close-btn" style="color: var(--danger);">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="navDistance" class="nav-distance"></div>
        <div id="navStepsList" class="nav-steps"></div>
        <div id="remainingDistance" class="nav-distance hidden" style="border-left-color: var(--info);">
            <i class="fas fa-route mr-2"></i> <span id="remainingDistValue">Calculating...</span>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" style="display: none;">
        <div class="loading">
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p style="color: #475569; font-size: 0.9rem;">Loading map...</p>
            </div>
        </div>
    </div>

    <script>
        // All existing JavaScript functionality from the original file
        let map;
        let markers = {};
        let officesData = [];
        let footwalkPaths = [];
        let pathGraph = {};
        let currentRoute = null;
        let userMarker = null;
        let userAccuracyCircle = null;
        let selectedOffice = null;
        let connectionPoints = [];
        let graphBuilt = false;
        let isTracking = false;
        let watchId = null;
        let currentRoutePoints = [];
        let remainingDistanceElem = document.getElementById('remainingDistance');
        let remainingDistValue = document.getElementById('remainingDistValue');
        
        let lastUserPosition = null;
        let routeRecalculationCount = 0;
        
        function initMap() {
            showLoading(true);
            const isMobile = window.innerWidth <= 640;
            const mapHeight = isMobile ? 'calc(100vh - 240px)' : 'calc(100vh - 0px)';
            document.getElementById('map').style.height = mapHeight;
            
            map = L.map('map').setView([9.853, 122.890], 18);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap',
                maxZoom: 19,
                detectRetina: true,
            }).addTo(map);
            
            loadOffices();
            loadFootwalks();
            
            window.addEventListener('resize', () => setTimeout(() => map.invalidateSize(), 100));
        }
        
        async function loadOffices() {
            try {
                const response = await fetch('/api/offices');
                const data = await response.json();
                if (data.success && data.data) {
                    officesData = data.data;
                    data.data.forEach(office => addMarkerToMap(office));
                }
            } catch (error) {
                console.error('Error loading offices:', error);
            }
        }
        
        function addMarkerToMap(office) {
            if (!office.lat || !office.lng) return;
            let markerColor = office.category === 'Administrative' ? '#ef4444' : 
                            office.category === 'Academic' ? '#3b82f6' : '#10b981';
            const customIcon = L.divIcon({
                html: `<div style="background-color: ${markerColor}; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>`,
                iconSize: [20, 20],
                className: 'custom-marker'
            });
            const marker = L.marker([office.lat, office.lng], { icon: customIcon }).addTo(map);
            marker.bindPopup(`
                <div class="p-2 min-w-[160px]">
                    <strong class="text-gray-800 text-sm">${office.name}</strong><br>
                    <span class="text-xs text-gray-600">${office.building}</span><br>
                    <button onclick="selectOffice('${office.office_id}')" class="mt-2 bg-blue-600 text-white px-3 py-1.5 rounded text-xs">
                        <i class="fas fa-directions mr-1"></i> Directions
                    </button>
                </div>
            `, { maxWidth: 250 });
            markers[office.office_id] = { marker: marker, data: office };
        }
        
        async function loadFootwalks() {
            try {
                const response = await fetch('/api/footwalks');
                const data = await response.json();
                if (data.success && data.data && data.data.length > 0) {
                    footwalkPaths = data.data.map(fw => ({
                        id: fw.id,
                        name: fw.name,
                        color: fw.color || '#3b82f6',
                        width: fw.width || 2,
                        coordinates: JSON.parse(fw.coordinates),
                        type: fw.type
                    }));
                    
                    footwalkPaths.forEach(path => drawFootwalkOnMap(path));
                    buildPathGraph();
                    showConnectionPoints();
                    graphBuilt = true;
                    showToast(`${footwalkPaths.length} footwalk paths loaded`, 'success');
                }
                showLoading(false);
            } catch (error) {
                console.error('Error loading footwalks:', error);
                showLoading(false);
            }
        }
        
        function drawFootwalkOnMap(path) {
            try {
                const latlngs = path.coordinates.map(c => [c[0], c[1]]);
                L.polyline(latlngs, {
                    color: path.color,
                    weight: path.width * 2 || 4,
                    opacity: 0.6,
                    smoothFactor: 1
                }).addTo(map);
            } catch (e) {}
        }
        
        function buildPathGraph() {
            pathGraph = {};
            let nodeId = 0;
            const pointToId = new Map();
            
            footwalkPaths.forEach(path => {
                for (let i = 0; i < path.coordinates.length; i++) {
                    const coord = path.coordinates[i];
                    const key = `${coord[0].toFixed(7)},${coord[1].toFixed(7)}`;
                    if (!pointToId.has(key)) {
                        const id = `n${nodeId++}`;
                        pointToId.set(key, id);
                        pathGraph[id] = { lat: coord[0], lng: coord[1], edges: [], pathId: path.id };
                    }
                }
            });
            
            footwalkPaths.forEach(path => {
                for (let i = 0; i < path.coordinates.length - 1; i++) {
                    const key1 = `${path.coordinates[i][0].toFixed(7)},${path.coordinates[i][1].toFixed(7)}`;
                    const key2 = `${path.coordinates[i+1][0].toFixed(7)},${path.coordinates[i+1][1].toFixed(7)}`;
                    const id1 = pointToId.get(key1);
                    const id2 = pointToId.get(key2);
                    if (id1 && id2) {
                        const dist = calculateDistance(path.coordinates[i][0], path.coordinates[i][1], path.coordinates[i+1][0], path.coordinates[i+1][1]);
                        if (!pathGraph[id1].edges.some(e => e.to === id2)) {
                            pathGraph[id1].edges.push({ to: id2, dist: dist });
                            pathGraph[id2].edges.push({ to: id1, dist: dist });
                        }
                    }
                }
            });
            
            const CONNECTION_DISTANCE = 15;
            for (let i = 0; i < footwalkPaths.length; i++) {
                for (let j = i + 1; j < footwalkPaths.length; j++) {
                    const path1 = footwalkPaths[i];
                    const path2 = footwalkPaths[j];
                    const closest = findClosestBetweenPaths(path1.coordinates, path2.coordinates);
                    
                    if (closest.distance < CONNECTION_DISTANCE) {
                        const point1Key = `${closest.point1.lat.toFixed(7)},${closest.point1.lng.toFixed(7)}`;
                        const point2Key = `${closest.point2.lat.toFixed(7)},${closest.point2.lng.toFixed(7)}`;
                        
                        let node1Id = pointToId.get(point1Key);
                        let node2Id = pointToId.get(point2Key);
                        
                        if (!node1Id) {
                            node1Id = `n${nodeId++}`;
                            pointToId.set(point1Key, node1Id);
                            pathGraph[node1Id] = { lat: closest.point1.lat, lng: closest.point1.lng, edges: [], pathId: path1.id, isConnection: true };
                            insertNodeIntoPath(path1.id, closest.point1, pointToId, pathGraph);
                        }
                        if (!node2Id) {
                            node2Id = `n${nodeId++}`;
                            pointToId.set(point2Key, node2Id);
                            pathGraph[node2Id] = { lat: closest.point2.lat, lng: closest.point2.lng, edges: [], pathId: path2.id, isConnection: true };
                            insertNodeIntoPath(path2.id, closest.point2, pointToId, pathGraph);
                        }
                        
                        const connectDist = calculateDistance(closest.point1.lat, closest.point1.lng, closest.point2.lat, closest.point2.lng);
                        if (!pathGraph[node1Id].edges.some(e => e.to === node2Id)) {
                            pathGraph[node1Id].edges.push({ to: node2Id, dist: connectDist });
                            pathGraph[node2Id].edges.push({ to: node1Id, dist: connectDist });
                        }
                        
                        connectionPoints.push({ lat: (closest.point1.lat + closest.point2.lat) / 2, lng: (closest.point1.lng + closest.point2.lng) / 2, paths: [path1.name, path2.name] });
                    }
                }
            }
        }
        
        function insertNodeIntoPath(pathId, point, pointToId, graph) {
            const path = footwalkPaths.find(p => p.id === pathId);
            if (!path) return;
            
            const pointKey = `${point.lat.toFixed(7)},${point.lng.toFixed(7)}`;
            const newNodeId = pointToId.get(pointKey);
            
            for (let i = 0; i < path.coordinates.length - 1; i++) {
                const start = path.coordinates[i];
                const end = path.coordinates[i+1];
                const distToStart = calculateDistance(point.lat, point.lng, start[0], start[1]);
                const distToEnd = calculateDistance(point.lat, point.lng, end[0], end[1]);
                const segmentLength = calculateDistance(start[0], start[1], end[0], end[1]);
                
                if (Math.abs(distToStart + distToEnd - segmentLength) < 2) {
                    const startKey = `${start[0].toFixed(7)},${start[1].toFixed(7)}`;
                    const endKey = `${end[0].toFixed(7)},${end[1].toFixed(7)}`;
                    const startNodeId = pointToId.get(startKey);
                    const endNodeId = pointToId.get(endKey);
                    
                    if (startNodeId && endNodeId) {
                        graph[startNodeId].edges = graph[startNodeId].edges.filter(e => e.to !== endNodeId);
                        graph[endNodeId].edges = graph[endNodeId].edges.filter(e => e.to !== startNodeId);
                        
                        const dist1 = calculateDistance(start[0], start[1], point.lat, point.lng);
                        const dist2 = calculateDistance(point.lat, point.lng, end[0], end[1]);
                        
                        graph[startNodeId].edges.push({ to: newNodeId, dist: dist1 });
                        graph[newNodeId].edges.push({ to: startNodeId, dist: dist1 });
                        graph[newNodeId].edges.push({ to: endNodeId, dist: dist2 });
                        graph[endNodeId].edges.push({ to: newNodeId, dist: dist2 });
                    }
                    break;
                }
            }
        }
        
        function findClosestBetweenPaths(coords1, coords2) {
            let minDist = Infinity;
            let bestPoint1 = { lat: coords1[0][0], lng: coords1[0][1] };
            let bestPoint2 = { lat: coords2[0][0], lng: coords2[0][1] };
            
            for (let i = 0; i < coords1.length - 1; i++) {
                for (let j = 0; j < coords2.length - 1; j++) {
                    for (let t1 = 0; t1 <= 20; t1++) {
                        const tVal1 = t1 / 20;
                        const lat1 = coords1[i][0] + tVal1 * (coords1[i+1][0] - coords1[i][0]);
                        const lng1 = coords1[i][1] + tVal1 * (coords1[i+1][1] - coords1[i][1]);
                        for (let t2 = 0; t2 <= 20; t2++) {
                            const tVal2 = t2 / 20;
                            const lat2 = coords2[j][0] + tVal2 * (coords2[j+1][0] - coords2[j][0]);
                            const lng2 = coords2[j][1] + tVal2 * (coords2[j+1][1] - coords2[j][1]);
                            const dist = calculateDistance(lat1, lng1, lat2, lng2);
                            if (dist < minDist) {
                                minDist = dist;
                                bestPoint1 = { lat: lat1, lng: lng1 };
                                bestPoint2 = { lat: lat2, lng: lng2 };
                            }
                        }
                    }
                }
            }
            return { point1: bestPoint1, point2: bestPoint2, distance: minDist };
        }
        
        function showConnectionPoints() {
            connectionPoints.forEach(point => {
                const icon = L.divIcon({
                    html: `<div style="background-color: #f59e0b; width: 10px; height: 10px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 5px rgba(0,0,0,0.3);"></div>`,
                    iconSize: [14, 14]
                });
                L.marker([point.lat, point.lng], { icon }).addTo(map);
            });
        }
        
        function calculateDistance(lat1, lng1, lat2, lng2) {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLng = (lng2 - lng1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLng/2) * Math.sin(dLng/2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        }
        
        function findNearestNodeOnPath(lat, lng) {
            let nearest = null;
            let minDist = Infinity;
            Object.keys(pathGraph).forEach(nodeId => {
                const node = pathGraph[nodeId];
                const dist = calculateDistance(lat, lng, node.lat, node.lng);
                if (dist < minDist) {
                    minDist = dist;
                    nearest = { id: nodeId, node: node, dist: dist };
                }
            });
            return nearest;
        }
        
        function findShortestPath(startLat, startLng, endLat, endLng) {
            if (Object.keys(pathGraph).length === 0) {
                return [[startLat, startLng], [endLat, endLng]];
            }
            
            const startNode = findNearestNodeOnPath(startLat, startLng);
            const endNode = findNearestNodeOnPath(endLat, endLng);
            
            if (!startNode || !endNode) {
                return [[startLat, startLng], [endLat, endLng]];
            }
            
            const distances = {};
            const previous = {};
            const unvisited = new Set();
            
            Object.keys(pathGraph).forEach(nodeId => {
                distances[nodeId] = Infinity;
                unvisited.add(nodeId);
            });
            distances[startNode.id] = 0;
            
            while (unvisited.size > 0) {
                let current = null;
                let minDist = Infinity;
                unvisited.forEach(nodeId => {
                    if (distances[nodeId] < minDist) {
                        minDist = distances[nodeId];
                        current = nodeId;
                    }
                });
                if (!current || current === endNode.id) break;
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
            
            if (distances[endNode.id] === Infinity) {
                return [[startLat, startLng], [endLat, endLng]];
            }
            
            const pathIds = [];
            let curr = endNode.id;
            while (curr && curr !== startNode.id) {
                pathIds.unshift(curr);
                curr = previous[curr];
                if (!curr) break;
            }
            pathIds.unshift(startNode.id);
            
            const points = [];
            points.push([startLat, startLng]);
            if (startNode.dist > 0.5 && pathGraph[startNode.id]) {
                points.push([pathGraph[startNode.id].lat, pathGraph[startNode.id].lng]);
            }
            pathIds.forEach(id => { if (pathGraph[id]) points.push([pathGraph[id].lat, pathGraph[id].lng]); });
            if (endNode.dist > 0.5 && pathGraph[endNode.id]) {
                points.push([pathGraph[endNode.id].lat, pathGraph[endNode.id].lng]);
            }
            points.push([endLat, endLng]);
            
            const unique = [];
            points.forEach(p => {
                if (unique.length === 0 || unique[unique.length-1][0] !== p[0] || unique[unique.length-1][1] !== p[1]) {
                    unique.push(p);
                }
            });
            return unique;
        }
        
        function selectOffice(officeId) {
            selectedOffice = officesData.find(o => o.office_id === officeId);
            if (selectedOffice) {
                document.getElementById('officeName').innerHTML = selectedOffice.name;
                document.getElementById('officeDetails').innerHTML = `
                    <div class="info-detail">
                        <i class="fas fa-building" style="color: var(--primary); min-width: 16px;"></i>
                        <span>${selectedOffice.building}</span>
                    </div>
                    <div class="info-detail">
                        <i class="fas fa-door-open" style="color: var(--primary); min-width: 16px;"></i>
                        <span>${selectedOffice.room_number || 'Ground Floor'}</span>
                    </div>
                    <div class="info-detail">
                        <i class="fas fa-clock" style="color: var(--primary); min-width: 16px;"></i>
                        <span>${selectedOffice.working_hours || '8:00 AM - 5:00 PM'}</span>
                    </div>
                    <span class="info-badge">${selectedOffice.category}</span>
                `;
                document.getElementById('infoPanel').classList.add('show');
                map.setView([selectedOffice.lat, selectedOffice.lng], 18);
                if (markers[officeId]) markers[officeId].marker.openPopup();
            }
        }
        
        function navigateToOffice() {
            if (!selectedOffice) {
                showToast('Please select an office first', 'error');
                return;
            }
            if (!graphBuilt || Object.keys(pathGraph).length === 0) {
                showToast('Footwalk paths are still loading...', 'error');
                return;
            }
            
            showToast('Getting your location...', 'info');
            
            navigator.geolocation.getCurrentPosition(pos => {
                const userLat = pos.coords.latitude;
                const userLng = pos.coords.longitude;
                calculateAndDrawRoute(userLat, userLng, selectedOffice.lat, selectedOffice.lng);
                
                if (!isTracking) {
                    startLiveTracking();
                }
            }, () => {
                showToast('Please enable location services', 'error');
            });
        }
        
        function calculateAndDrawRoute(startLat, startLng, endLat, endLng, isRecalculation = false) {
            const pathPoints = findShortestPath(startLat, startLng, endLat, endLng);
            currentRoutePoints = pathPoints;
            
            if (currentRoute && map.hasLayer(currentRoute)) map.removeLayer(currentRoute);
            currentRoute = L.polyline(pathPoints, {
                color: '#22c55e',
                weight: 5,
                dashArray: '10, 10',
                opacity: 0.9,
                lineCap: 'round',
                lineJoin: 'round'
            }).addTo(map);
            
            if (!isRecalculation) {
                map.fitBounds(currentRoute.getBounds());
            }
            
            updateUserMarker(startLat, startLng);
            
            let remainingDist = 0;
            for (let i = 0; i < pathPoints.length - 1; i++) {
                remainingDist += calculateDistance(pathPoints[i][0], pathPoints[i][1], pathPoints[i+1][0], pathPoints[i+1][1]);
            }
            
            document.getElementById('navInstructions').classList.add('show');
            document.getElementById('navDistance').innerHTML = `<i class="fas fa-route mr-2"></i> <strong>${Math.round(remainingDist)}m away</strong> (${(remainingDist / 80).toFixed(1)} min walk)`;
            document.getElementById('navStepsList').innerHTML = `
                <div class="nav-step">
                    <div class="nav-icon"><i class="fas fa-location-dot" style="color: var(--accent);"></i></div>
                    <div>You are here</div>
                </div>
                <div class="nav-step">
                    <div class="nav-icon"><i class="fas fa-road" style="color: var(--primary);"></i></div>
                    <div>Follow the green path along footwalks</div>
                </div>
                ${connectionPoints.length > 0 ? `<div class="nav-step">
                    <div class="nav-icon"><i class="fas fa-exchange-alt" style="color: var(--warning);"></i></div>
                    <div>Paths connect at connection points</div>
                </div>` : ''}
                <div class="nav-step">
                    <div class="nav-icon"><i class="fas fa-flag-checkered" style="color: var(--danger);"></i></div>
                    <div>Arrive at ${selectedOffice.name}</div>
                </div>
            `;
            
            remainingDistanceElem.classList.remove('hidden');
            remainingDistValue.innerHTML = `${Math.round(remainingDist)}m remaining (${(remainingDist / 80).toFixed(1)} min)`;
            
            closePanel();
            
            if (!isRecalculation) {
                showToast(`Route found! ${Math.round(remainingDist)}m to destination`, 'success');
            }
        }
        
        function updateRemainingDistance(currentLat, currentLng) {
            if (!currentRoutePoints || currentRoutePoints.length < 2 || !selectedOffice) return;
            
            let minDistToRoute = Infinity;
            let closestIndex = 0;
            
            for (let i = 0; i < currentRoutePoints.length; i++) {
                const dist = calculateDistance(currentLat, currentLng, currentRoutePoints[i][0], currentRoutePoints[i][1]);
                if (dist < minDistToRoute) {
                    minDistToRoute = dist;
                    closestIndex = i;
                }
            }
            
            let remainingDist = 0;
            for (let i = closestIndex; i < currentRoutePoints.length - 1; i++) {
                remainingDist += calculateDistance(currentRoutePoints[i][0], currentRoutePoints[i][1], currentRoutePoints[i+1][0], currentRoutePoints[i+1][1]);
            }
            
            remainingDistValue.innerHTML = `${Math.round(remainingDist)}m remaining (${(remainingDist / 80).toFixed(1)} min)`;
            
            const distToDest = calculateDistance(currentLat, currentLng, selectedOffice.lat, selectedOffice.lng);
            if (distToDest < 10) {
                showToast(`🎉 You have arrived at ${selectedOffice.name}!`, 'success');
                stopNavigation();
                stopLiveTracking();
            }
        }
        
        function updateUserMarker(lat, lng, accuracy = null) {
            if (userMarker && map.hasLayer(userMarker)) map.removeLayer(userMarker);
            if (userAccuracyCircle && map.hasLayer(userAccuracyCircle)) map.removeLayer(userAccuracyCircle);
            
            const userIcon = L.divIcon({
                html: '<div class="user-marker" style="width: 20px; height: 20px;"></div>',
                iconSize: [20, 20],
                className: 'user-marker-container'
            });
            
            userMarker = L.marker([lat, lng], { icon: userIcon }).addTo(map);
            
            if (accuracy && accuracy < 100) {
                userAccuracyCircle = L.circle([lat, lng], {
                    radius: accuracy,
                    color: '#22c55e',
                    weight: 1,
                    opacity: 0.3,
                    fillColor: '#22c55e',
                    fillOpacity: 0.1
                }).addTo(map);
            }
        }
        
        function startLiveTracking() {
            if (watchId !== null) return;
            
            isTracking = true;
            document.getElementById('liveBadge').classList.remove('hidden');
            document.getElementById('gpsIndicator').classList.add('active');
            
            showToast('Live GPS tracking activated', 'success');
            
            watchId = navigator.geolocation.watchPosition(
                (position) => {
                    const { latitude, longitude, accuracy } = position.coords;
                    lastUserPosition = { lat: latitude, lng: longitude };
                    
                    updateUserMarker(latitude, longitude, accuracy);
                    
                    if (selectedOffice && currentRoute) {
                        updateRemainingDistance(latitude, longitude);
                        
                        routeRecalculationCount++;
                        if (routeRecalculationCount >= 10) {
                            routeRecalculationCount = 0;
                            const distToRoute = calculateDistanceToRoute(latitude, longitude);
                            if (distToRoute > 15) {
                                showToast('Recalculating route...', 'info');
                                calculateAndDrawRoute(latitude, longitude, selectedOffice.lat, selectedOffice.lng, true);
                            }
                        }
                        
                        if (isTracking) {
                            map.setView([latitude, longitude], 18);
                        }
                    }
                },
                (error) => {
                    console.error('GPS error:', error);
                    if (error.code === 1) {
                        showToast('Please enable GPS for live tracking', 'error');
                        stopLiveTracking();
                    }
                },
                {
                    enableHighAccuracy: true,
                    maximumAge: 2000,
                    timeout: 10000
                }
            );
        }
        
        function calculateDistanceToRoute(lat, lng) {
            if (!currentRoutePoints) return Infinity;
            let minDist = Infinity;
            for (let i = 0; i < currentRoutePoints.length - 1; i++) {
                const dist = distanceToSegment(lat, lng, 
                    currentRoutePoints[i][0], currentRoutePoints[i][1],
                    currentRoutePoints[i+1][0], currentRoutePoints[i+1][1]);
                if (dist < minDist) minDist = dist;
            }
            return minDist;
        }
        
        function distanceToSegment(px, py, x1, y1, x2, y2) {
            const dx = x2 - x1;
            const dy = y2 - y1;
            const t = ((px - x1) * dx + (py - y1) * dy) / (dx * dx + dy * dy);
            if (t < 0) return calculateDistance(px, py, x1, y1);
            if (t > 1) return calculateDistance(px, py, x2, y2);
            const ix = x1 + t * dx;
            const iy = y1 + t * dy;
            return calculateDistance(px, py, ix, iy);
        }
        
        function stopLiveTracking() {
            if (watchId !== null) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }
            isTracking = false;
            document.getElementById('liveBadge').classList.add('hidden');
            document.getElementById('gpsIndicator').classList.remove('active');
        }
        
        function toggleLiveTracking() {
            if (isTracking) {
                stopLiveTracking();
                showToast('Live tracking stopped', 'info');
            } else {
                startLiveTracking();
            }
        }
        
        function centerToUser() {
            if (!isTracking) {
                navigator.geolocation.getCurrentPosition(pos => {
                    map.setView([pos.coords.latitude, pos.coords.longitude], 18);
                    updateUserMarker(pos.coords.latitude, pos.coords.longitude);
                    showToast('Location found!', 'success');
                    if (!isTracking) startLiveTracking();
                }, () => showToast('Unable to get location', 'error'));
            } else {
                if (lastUserPosition) {
                    map.setView([lastUserPosition.lat, lastUserPosition.lng], 18);
                }
            }
        }
        
        function stopNavigation() {
            if (currentRoute && map.hasLayer(currentRoute)) map.removeLayer(currentRoute);
            if (userMarker && map.hasLayer(userMarker)) map.removeLayer(userMarker);
            if (userAccuracyCircle && map.hasLayer(userAccuracyCircle)) map.removeLayer(userAccuracyCircle);
            document.getElementById('navInstructions').classList.remove('show');
            remainingDistanceElem.classList.add('hidden');
            currentRoute = null;
            currentRoutePoints = [];
            stopLiveTracking();
            showToast('Navigation stopped', 'info');
        }
        
        function findNearestOffice() {
            navigator.geolocation.getCurrentPosition(pos => {
                const userLat = pos.coords.latitude, userLng = pos.coords.longitude;
                let nearest = null, minDist = Infinity;
                officesData.forEach(office => {
                    const dist = calculateDistance(userLat, userLng, office.lat, office.lng);
                    if (dist < minDist) { minDist = dist; nearest = office; }
                });
                if (nearest) {
                    showToast(`Nearest: ${nearest.name} (${Math.round(minDist)}m away)`, 'success');
                    selectOffice(nearest.office_id);
                }
            }, () => showToast('Enable location to find nearest office', 'error'));
        }
        
        function resetMap() {
            map.setView([9.853, 122.890], 18);
            stopNavigation();
            closePanel();
            stopLiveTracking();
            if (userMarker && map.hasLayer(userMarker)) map.removeLayer(userMarker);
            userMarker = null;
        }
        
        function closePanel() {
            document.getElementById('infoPanel').classList.remove('show');
            selectedOffice = null;
        }
        
        function showToast(message, type) {
            const toast = document.createElement('div');
            const toastClass = type === 'success' ? 'toast-success' : type === 'error' ? 'toast-error' : 'toast-info';
            const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle';
            
            toast.className = `toast ${toastClass}`;
            toast.innerHTML = `<i class="fas fa-${icon}"></i><span>${message}</span>`;
            document.body.appendChild(toast);
            
            setTimeout(() => toast.remove(), 3000);
        }
        
        function showLoading(show) {
            const loader = document.getElementById('loadingIndicator');
            if (show) {
                loader.style.display = 'flex';
            } else {
                loader.style.display = 'none';
            }
        }
        
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            if (term.length < 2) return;
            const matched = officesData.filter(o => o.name.toLowerCase().includes(term) || o.building.toLowerCase().includes(term));
            if (matched.length) {
                const bounds = L.latLngBounds();
                matched.forEach(o => { if (markers[o.office_id]) bounds.extend(markers[o.office_id].marker.getLatLng()); });
                map.fitBounds(bounds);
                showToast(`Found ${matched.length} office(s)`, 'success');
            }
        });
        
        document.addEventListener('DOMContentLoaded', initMap);
    </script>
</body>
</html>
