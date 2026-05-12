<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>CPSU Map Navigator - Live GPS Navigation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            -webkit-tap-highlight-color: transparent;
        }
        
        body {
            overflow-x: hidden;
            position: fixed;
            width: 100%;
            height: 100%;
        }
        
        #map { 
            height: calc(100vh - 130px); 
            width: 100%;
            border-radius: 0;
            z-index: 1;
        }
        
        @media (max-width: 640px) {
            .info-panel {
                bottom: 10px;
                right: 10px;
                left: 10px;
                max-width: none;
                width: auto;
                max-height: 50vh;
                overflow-y: auto;
                font-size: 14px;
            }
            
            .nav-instructions {
                bottom: 10px;
                left: 10px;
                right: 10px;
                max-width: none;
                width: auto;
                max-height: 45vh;
                overflow-y: auto;
            }
            
            button {
                min-height: 44px;
                min-width: 44px;
            }
            
            .custom-marker {
                width: 24px !important;
                height: 24px !important;
            }
            
            .legend {
                gap: 8px;
                overflow-x: auto;
                white-space: nowrap;
                flex-wrap: nowrap;
                padding-bottom: 4px;
            }
            
            .legend-item {
                flex-shrink: 0;
            }
            
            .gps-indicator {
                bottom: 90px;
                right: 10px;
                top: auto;
            }
        }
        
        @media (min-width: 641px) {
            #map {
                height: 65vh;
                border-radius: 12px;
            }
            
            .info-panel {
                max-width: 320px;
                left: auto;
            }
            
            .nav-instructions {
                max-width: 380px;
            }
            
            .gps-indicator {
                top: 20px;
                right: 20px;
            }
        }
        
        .info-panel {
            position: fixed;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            padding: 15px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            display: none;
            border-left: 4px solid #3b82f6;
            z-index: 1000;
        }
        
        .info-panel.show { display: block; animation: slideIn 0.3s ease; }
        
        @keyframes slideIn {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .custom-marker { cursor: pointer; transition: transform 0.2s; }
        
        .loading {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2000;
            background: white;
            padding: 20px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        
        .nav-instructions {
            position: fixed;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            display: none;
            z-index: 1000;
        }
        
        .nav-instructions.show { display: block; animation: slideUp 0.3s ease; }
        
        @keyframes slideUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes dash {
            to { stroke-dashoffset: -20; }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 0.7; }
        }
        
        @keyframes ripples {
            0% { transform: scale(1); opacity: 0.5; }
            100% { transform: scale(2.5); opacity: 0; }
        }
        
        .route-shortest { 
            stroke: #22c55e; 
            stroke-dasharray: 10, 10; 
            animation: dash 1s linear infinite; 
        }
        
        .user-location-marker {
            background: #22c55e;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            border: 3px solid white;
            box-shadow: 0 0 10px rgba(34,197,94,0.5);
            animation: pulse 2s ease infinite;
        }
        
        .user-location-marker::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: #22c55e;
            transform: translate(-50%, -50%);
            animation: ripples 2s ease-out infinite;
        }
        
        .accuracy-circle {
            background: rgba(34, 197, 94, 0.15);
            border-radius: 50%;
            pointer-events: none;
        }
        
        .touch-btn {
            touch-action: manipulation;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .touch-btn:active {
            transform: scale(0.96);
        }
        
        .gps-indicator {
            position: fixed;
            background: white;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            cursor: pointer;
            z-index: 1000;
            transition: all 0.2s;
        }
        
        .gps-indicator.active {
            background: #22c55e;
            color: white;
        }
        
        .gps-indicator:active {
            transform: scale(0.9);
        }
        
        .live-track-badge {
            background: #22c55e;
            color: white;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            margin-left: 8px;
            animation: pulse 1s ease infinite;
        }
        
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        
        @supports (padding-bottom: env(safe-area-inset-bottom)) {
            .info-panel, .nav-instructions {
                margin-bottom: env(safe-area-inset-bottom);
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Mobile Navigation Bar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <span class="text-2xl mr-2">🗺️</span>
                <h1 class="text-lg font-bold text-gray-800">CPSU Navigator</h1>
                <span id="liveBadge" class="live-track-badge hidden" style="background: #22c55e;">
                    <i class="fas fa-satellite-dish mr-1"></i> LIVE
                </span>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ url('/directory') }}" class="text-gray-600 hover:text-blue-600 text-sm">
                    <i class="fas fa-list"></i>
                </a>
                <a href="{{ url('/login') }}" class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-sm">Admin</a>
            </div>
        </div>
    </nav>

    <!-- Search and Controls -->
    <div class="sticky top-0 z-40 bg-white shadow-md px-3 py-2">
        <div class="flex flex-col gap-2">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" id="searchInput" placeholder="Search for offices..." 
                       class="w-full pl-9 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="flex gap-2">
                <button onclick="centerToUser()" class="flex-1 bg-green-600 text-white py-2.5 rounded-lg text-sm touch-btn">
                    <i class="fas fa-location-dot mr-1"></i> My Location
                </button>
                <button onclick="findNearestOffice()" class="flex-1 bg-purple-600 text-white py-2.5 rounded-lg text-sm touch-btn">
                    <i class="fas fa-building mr-1"></i> Nearest
                </button>
                <button onclick="resetMap()" class="flex-1 bg-gray-600 text-white py-2.5 rounded-lg text-sm touch-btn">
                    <i class="fas fa-home mr-1"></i> Reset
                </button>
            </div>
            
            <div class="legend flex gap-3 pt-1 pb-1 overflow-x-auto">
                <div class="legend-item flex items-center"><div class="w-2.5 h-2.5 bg-red-500 rounded-full mr-1"></div><span class="text-xs">Admin</span></div>
                <div class="legend-item flex items-center"><div class="w-2.5 h-2.5 bg-blue-500 rounded-full mr-1"></div><span class="text-xs">Academic</span></div>
                <div class="legend-item flex items-center"><div class="w-2.5 h-2.5 bg-green-500 rounded-full mr-1"></div><span class="text-xs">Facility</span></div>
                <div class="legend-item flex items-center"><i class="fas fa-road text-blue-500 mr-1 text-xs"></i><span class="text-xs">Footwalk</span></div>
                <div class="legend-item flex items-center"><i class="fas fa-route text-green-500 mr-1 text-xs"></i><span class="text-xs">Your Path</span></div>
                <div class="legend-item flex items-center"><div class="w-1.5 h-1.5 bg-orange-500 rounded-full mr-1"></div><span class="text-xs">Connection</span></div>
            </div>
        </div>
    </div>

    <!-- Map Container -->
    <div id="map"></div>
    
    <!-- GPS Live Tracking Indicator -->
    <div id="gpsIndicator" class="gps-indicator" onclick="toggleLiveTracking()">
        <i class="fas fa-location-arrow text-lg"></i>
    </div>
    
    <!-- Info Panel -->
    <div id="infoPanel" class="info-panel">
        <div class="flex justify-between items-start mb-2">
            <h3 id="officeName" class="font-bold text-base text-gray-800 flex-1 pr-2"></h3>
            <button onclick="closePanel()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <div id="officeDetails" class="text-sm text-gray-600 space-y-1"></div>
        <button onclick="navigateToOffice()" class="w-full bg-blue-600 text-white py-2.5 rounded-lg text-sm mt-3 touch-btn">
            <i class="fas fa-directions mr-1"></i> Navigate to this Office
        </button>
    </div>
    
    <!-- Navigation Instructions -->
    <div id="navInstructions" class="nav-instructions">
        <div class="p-3">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center">
                    <i class="fas fa-walking text-blue-500 text-lg mr-2"></i>
                    <span class="font-semibold text-sm">Walking Directions</span>
                </div>
                <button onclick="stopNavigation()" class="text-red-500 hover:text-red-700 p-1 touch-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="navDistance" class="text-xs text-gray-600 mb-2"></div>
            <div id="navStepsList" class="text-xs text-gray-500 space-y-1 max-h-32 overflow-y-auto"></div>
            <div id="remainingDistance" class="text-xs bg-blue-50 p-2 rounded mt-2 hidden">
                <i class="fas fa-route mr-1 text-blue-500"></i> <span id="remainingDistValue">Calculating...</span>
            </div>
        </div>
    </div>

    <script>
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
        
        // Live tracking variables
        let lastUserPosition = null;
        let routeRecalculationCount = 0;
        
        function initMap() {
            showLoading(true);
            const isMobile = window.innerWidth <= 640;
            document.getElementById('map').style.height = isMobile ? 'calc(100vh - 130px)' : '65vh';
            
            map = L.map('map').setView([9.853, 122.890], 18);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19,
                detectRetina: true,
                updateWhenIdle: true,
                updateWhenZooming: false
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
            const isMobile = window.innerWidth <= 640;
            const markerSize = isMobile ? 22 : 18;
            let markerColor = office.category === 'Administrative' ? '#ef4444' : office.category === 'Academic' ? '#3b82f6' : '#10b981';
            const customIcon = L.divIcon({
                html: `<div style="background-color: ${markerColor}; width: ${markerSize}px; height: ${markerSize}px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>`,
                iconSize: [markerSize, markerSize],
                className: 'custom-marker'
            });
            const marker = L.marker([office.lat, office.lng], { icon: customIcon }).addTo(map);
            marker.bindPopup(`
                <div class="p-2 min-w-[160px]">
                    <strong class="text-gray-800 text-sm">${office.name}</strong><br>
                    <span class="text-xs text-gray-600">${office.building}</span><br>
                    <button onclick="selectOffice('${office.office_id}')" class="mt-2 bg-blue-600 text-white px-3 py-1.5 rounded text-xs touch-btn">
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
                } else {
                    showToast('No footwalks found. Admin needs to add pathways.', 'info');
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
                    html: `<div style="background-color: #ff9800; width: 10px; height: 10px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 5px rgba(0,0,0,0.3);"></div>`,
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
                    <p class="text-sm"><i class="fas fa-building mr-2 w-4"></i>${selectedOffice.building}</p>
                    <p class="text-sm"><i class="fas fa-door-open mr-2 w-4"></i>${selectedOffice.room_number || 'Ground Floor'}</p>
                    <p><span class="px-2 py-0.5 rounded-full text-xs" style="background:#dbeafe">${selectedOffice.category}</span></p>
                    <p class="text-sm"><i class="far fa-clock mr-2 w-4"></i>${selectedOffice.working_hours || '8:00 AM - 5:00 PM'}</p>
                `;
                document.getElementById('infoPanel').classList.add('show');
                map.setView([selectedOffice.lat, selectedOffice.lng], 18);
                markers[officeId].marker.openPopup();
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
                
                // Start live tracking if not already active
                if (!isTracking) {
                    startLiveTracking();
                }
            }, () => {
                showToast('Please enable location services for navigation', 'error');
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
                className: 'route-shortest'
            }).addTo(map);
            
            if (!isRecalculation) {
                map.fitBounds(currentRoute.getBounds());
            }
            
            // Update user marker if exists
            updateUserMarker(startLat, startLng);
            
            // Calculate remaining distance
            let remainingDist = 0;
            for (let i = 0; i < pathPoints.length - 1; i++) {
                remainingDist += calculateDistance(pathPoints[i][0], pathPoints[i][1], pathPoints[i+1][0], pathPoints[i+1][1]);
            }
            
            // Update navigation UI
            document.getElementById('navInstructions').classList.add('show');
            document.getElementById('navDistance').innerHTML = `<i class="fas fa-route mr-1"></i> Total Distance: ${Math.round(remainingDist)} meters (${(remainingDist / 80).toFixed(1)} min walk)`;
            document.getElementById('navStepsList').innerHTML = `
                <div class="py-1"><i class="fas fa-location-dot mr-2 text-green-500 w-4"></i> Your current location</div>
                <div class="py-1"><i class="fas fa-road mr-2 text-blue-500 w-4"></i> Follow the green path along footwalks</div>
                ${connectionPoints.length > 0 ? '<div class="py-1"><i class="fas fa-exchange-alt mr-2 text-orange-500 w-4"></i> Paths connect at orange points</div>' : ''}
                <div class="py-1"><i class="fas fa-flag-checkered mr-2 text-red-500 w-4"></i> Arrive at ${selectedOffice.name}</div>
            `;
            
            remainingDistanceElem.classList.remove('hidden');
            remainingDistValue.innerHTML = `${Math.round(remainingDist)} meters remaining (${(remainingDist / 80).toFixed(1)} min)`;
            
            closePanel();
            
            if (!isRecalculation) {
                showToast(`Route found! ${Math.round(remainingDist)} meters to destination`, 'success');
            }
        }
        
        function updateRemainingDistance(currentLat, currentLng) {
            if (!currentRoutePoints || currentRoutePoints.length < 2 || !selectedOffice) return;
            
            // Find closest point on route to current position
            let minDistToRoute = Infinity;
            let closestIndex = 0;
            
            for (let i = 0; i < currentRoutePoints.length; i++) {
                const dist = calculateDistance(currentLat, currentLng, currentRoutePoints[i][0], currentRoutePoints[i][1]);
                if (dist < minDistToRoute) {
                    minDistToRoute = dist;
                    closestIndex = i;
                }
            }
            
            // Calculate remaining distance from closest point to destination
            let remainingDist = 0;
            for (let i = closestIndex; i < currentRoutePoints.length - 1; i++) {
                remainingDist += calculateDistance(currentRoutePoints[i][0], currentRoutePoints[i][1], currentRoutePoints[i+1][0], currentRoutePoints[i+1][1]);
            }
            
            remainingDistValue.innerHTML = `${Math.round(remainingDist)} meters remaining (${(remainingDist / 80).toFixed(1)} min)`;
            
            // Check if destination reached (within 10 meters)
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
                html: '<div class="user-location-marker"></div>',
                iconSize: [20, 20],
                className: 'user-marker'
            });
            
            userMarker = L.marker([lat, lng], { icon: userIcon }).addTo(map);
            userMarker.bindPopup('<b>You are here</b>').openPopup();
            
            if (accuracy && accuracy < 100) {
                userAccuracyCircle = L.circle([lat, lng], {
                    radius: accuracy,
                    color: '#22c55e',
                    weight: 1,
                    opacity: 0.3,
                    fillColor: '#22c55e',
                    fillOpacity: 0.1,
                    className: 'accuracy-circle'
                }).addTo(map);
            }
        }
        
        function startLiveTracking() {
            if (watchId !== null) return;
            
            isTracking = true;
            document.getElementById('liveBadge').classList.remove('hidden');
            document.getElementById('gpsIndicator').classList.add('active');
            document.getElementById('gpsIndicator').innerHTML = '<i class="fas fa-satellite-dish text-lg"></i>';
            
            showToast('Live GPS tracking activated - following your movement', 'success');
            
            watchId = navigator.geolocation.watchPosition(
                (position) => {
                    const { latitude, longitude, accuracy } = position.coords;
                    lastUserPosition = { lat: latitude, lng: longitude };
                    
                    updateUserMarker(latitude, longitude, accuracy);
                    
                    if (selectedOffice && currentRoute) {
                        updateRemainingDistance(latitude, longitude);
                        
                        // Recalculate route every 10 seconds or when significantly off course
                        routeRecalculationCount++;
                        if (routeRecalculationCount >= 10) {
                            routeRecalculationCount = 0;
                            const distToRoute = calculateDistanceToRoute(latitude, longitude);
                            if (distToRoute > 15) {
                                showToast('Recalculating route...', 'info');
                                calculateAndDrawRoute(latitude, longitude, selectedOffice.lat, selectedOffice.lng, true);
                            }
                        }
                        
                        // Auto-center map on user if tracking is active
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
            document.getElementById('gpsIndicator').innerHTML = '<i class="fas fa-location-arrow text-lg"></i>';
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
        
        function closePanel() { document.getElementById('infoPanel').classList.remove('show'); selectedOffice = null; }
        
        function showToast(message, type) {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-24 left-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-white text-sm ${type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500'} text-center`;
            toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>${message}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
        
        function showLoading(show) {
            const loader = document.getElementById('loadingIndicator');
            if (show && !loader) {
                const div = document.createElement('div');
                div.id = 'loadingIndicator';
                div.className = 'loading';
                div.innerHTML = '<i class="fas fa-spinner fa-spin text-2xl text-blue-500 mb-2"></i><p class="text-sm">Loading map...</p>';
                document.body.appendChild(div);
            } else if (!show && loader) loader.remove();
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