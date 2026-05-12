<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - CPSU Map Navigator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        #map { height: 550px; border-radius: 12px; z-index: 1; }
        .modal { transition: all 0.3s ease; }
        .tab-active { border-bottom: 3px solid #3b82f6; color: #2563eb; }
        .sidebar { scrollbar-width: thin; }
        .sidebar::-webkit-scrollbar { width: 6px; }
        .drawing-mode-active { background-color: #22c55e !important; }
        .path-node { cursor: pointer; transition: transform 0.2s; }
        .path-node:hover { transform: scale(1.2); }
        .intersection-marker {
            background: #ff9800;
            border-radius: 50%;
            width: 8px;
            height: 8px;
            border: 2px solid white;
            box-shadow: 0 0 5px rgba(0,0,0,0.3);
            cursor: pointer;
        }
        .intersection-marker:hover {
            transform: scale(1.5);
            background: #ff5722;
        }
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 10000;
            padding: 12px 20px;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .toast-success { background: #10b981; }
        .toast-error { background: #ef4444; }
        .toast-info { background: #3b82f6; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-80 bg-white shadow-xl flex flex-col sidebar overflow-y-auto">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center">
                    <span class="text-3xl mr-2">🗺️</span>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">CPSU Navigator Admin</h1>
                        <p class="text-xs text-gray-500">Pathway & Footwalk Manager</p>
                    </div>
                </div>
            </div>
            
            <!-- Tabs -->
            <div class="flex border-b border-gray-200">
                <button onclick="switchTab('offices')" id="tabOfficesBtn" class="flex-1 py-3 text-center font-medium transition tab-active">
                    🏢 Offices
                </button>
                <button onclick="switchTab('paths')" id="tabPathsBtn" class="flex-1 py-3 text-center font-medium text-gray-600 hover:text-gray-800 transition">
                    🛤️ Footwalks
                </button>
                <button onclick="switchTab('connections')" id="tabConnectionsBtn" class="flex-1 py-3 text-center font-medium text-gray-600 hover:text-gray-800 transition">
                    🔗 Connections
                </button>
            </div>
            
            <!-- Offices Tab -->
            <div id="officesTab" class="p-4">
                <button onclick="showOfficeModal()" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition mb-4 flex items-center justify-center">
                    <i class="fas fa-plus mr-2"></i> Add New Office
                </button>
                
                <div class="relative mb-3">
                    <input type="text" id="searchOffice" placeholder="Search offices..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm pl-8">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                </div>
                
                <div id="officesList" class="space-y-2 max-h-[calc(100vh-320px)] overflow-y-auto">
                    <!-- Offices will be loaded here -->
                </div>
            </div>
            
            <!-- Footwalks Tab -->
            <div id="pathsTab" class="p-4 hidden">
                <div class="bg-blue-50 rounded-lg p-3 mb-4 text-sm">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    <span class="text-gray-700">Draw footwalks - paths automatically connect when they cross or come within 5 meters!</span>
                </div>
                
                <button id="drawPathBtn" onclick="toggleDrawingMode()" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition mb-4 flex items-center justify-center">
                    <i class="fas fa-draw-polygon mr-2"></i> Draw New Footwalk
                </button>
                
                <div class="relative mb-3">
                    <input type="text" id="searchPath" placeholder="Search footwalks..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm pl-8">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                </div>
                
                <div id="pathsList" class="space-y-2 max-h-[calc(100vh-320px)] overflow-y-auto">
                    <!-- Footwalks will be loaded here -->
                </div>
                
                <!-- Connection Stats -->
                <div class="mt-4 bg-gray-50 rounded-lg p-3 text-xs">
                    <div class="flex justify-between items-center">
                        <span><i class="fas fa-link text-green-500"></i> Auto-Connect Distance:</span>
                        <span><input type="number" id="connectionDistance" value="5" step="1" class="w-16 border rounded px-1 text-center"> meters</span>
                    </div>
                    <div class="flex justify-between items-center mt-2">
                        <span><i class="fas fa-road text-blue-500"></i> Connected Paths:</span>
                        <span id="connectionCount">0</span>
                    </div>
                    <button onclick="recalculateConnections()" class="w-full mt-2 bg-purple-100 text-purple-700 py-1 rounded text-xs hover:bg-purple-200">
                        <i class="fas fa-sync-alt mr-1"></i> Recalculate Connections
                    </button>
                </div>
            </div>
            
            <!-- Connections Tab -->
            <div id="connectionsTab" class="p-4 hidden">
                <div class="bg-green-50 rounded-lg p-3 mb-4 text-sm">
                    <i class="fas fa-share-alt text-green-500 mr-2"></i>
                    <span>Connection points between footwalks - students can transfer at these points!</span>
                </div>
                <div id="connectionsList" class="space-y-2 max-h-[calc(100vh-300px)] overflow-y-auto">
                    <!-- Connections will be shown here -->
                </div>
            </div>
            
            <!-- Logout -->
            <div class="p-4 border-t border-gray-200 mt-auto">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full bg-red-50 text-red-600 py-2 rounded-lg hover:bg-red-100 transition flex items-center justify-center">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Main Content - Map -->
        <div class="flex-1 p-4">
            <div class="bg-white rounded-lg shadow-lg p-4 mb-4">
                <div class="flex justify-between items-center flex-wrap gap-2">
                    <div>
                        <h2 class="text-lg font-bold">Campus Pathway Editor</h2>
                        <p class="text-xs text-gray-500">Click on map to add office | Draw lines to create footwalks | Paths auto-connect within <span id="showConnectDist">5</span> meters</p>
                    </div>
                    <div class="flex gap-2">
                        <div id="drawStatus" class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                            <i class="fas fa-pencil-alt mr-1"></i> Drawing: OFF
                        </div>
                        <button onclick="showAllConnections()" class="bg-orange-500 text-white px-3 py-1 rounded text-sm hover:bg-orange-600">
                            <i class="fas fa-link mr-1"></i> Show Connections
                        </button>
                        <button onclick="resetMapView()" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                            <i class="fas fa-home mr-1"></i> Reset View
                        </button>
                    </div>
                </div>
            </div>
            <div id="map" class="shadow-lg"></div>
            <div class="text-xs text-gray-500 text-center mt-2 flex justify-center gap-4">
                <span><i class="fas fa-map-marker-alt text-red-500"></i> Office Markers</span>
                <span><i class="fas fa-draw-polygon text-blue-500"></i> Footwalk Paths</span>
                <span><i class="fas fa-circle text-orange-500"></i> Connection Points</span>
                <span><i class="fas fa-route text-green-500"></i> Students can walk between connected paths</span>
            </div>
        </div>
    </div>

    <!-- Office Modal -->
    <div id="officeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 modal">
        <div class="bg-white rounded-lg w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold" id="officeModalTitle">Add New Office</h3>
                    <button onclick="closeOfficeModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <form id="officeForm">
                    <input type="hidden" id="officeId">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Office Name *</label>
                            <input type="text" id="officeName" required class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Building *</label>
                            <input type="text" id="officeBuilding" required class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Floor/Room</label>
                            <input type="text" id="officeRoom" placeholder="e.g., 2nd Floor, Room 201" class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Category</label>
                            <select id="officeCategory" class="w-full border rounded-lg px-3 py-2 text-sm">
                                <option value="Administrative">Administrative</option>
                                <option value="Academic">Academic</option>
                                <option value="Facility">Facility</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Working Hours</label>
                            <input type="text" id="officeHours" placeholder="8:00 AM - 5:00 PM" class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Contact Number</label>
                            <input type="text" id="officeContact" class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium mb-1">Latitude</label>
                                <input type="text" id="officeLat" readonly class="w-full border rounded-lg px-3 py-2 text-sm bg-gray-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Longitude</label>
                                <input type="text" id="officeLng" readonly class="w-full border rounded-lg px-3 py-2 text-sm bg-gray-50">
                            </div>
                        </div>
                        <div class="text-xs text-gray-500">
                            <i class="fas fa-info-circle"></i> Click on the map to set location
                        </div>
                    </div>
                    <div class="flex gap-2 mt-6">
                        <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">Save</button>
                        <button type="button" onclick="closeOfficeModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-400 transition">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Path Modal -->
    <div id="pathModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 modal">
        <div class="bg-white rounded-lg w-full max-w-md mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold" id="pathModalTitle">Footwalk Details</h3>
                    <button onclick="closePathModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <form id="pathForm">
                    <input type="hidden" id="pathId">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Path Name *</label>
                            <input type="text" id="pathName" required class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="e.g., Main Walkway">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Path Type</label>
                            <select id="pathType" class="w-full border rounded-lg px-3 py-2 text-sm">
                                <option value="walkway">Walkway</option>
                                <option value="paved">Paved Path</option>
                                <option value="gravel">Gravel Path</option>
                                <option value="stairs">Stairs</option>
                                <option value="ramp">Ramp (Accessible)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Surface Color</label>
                            <input type="color" id="pathColor" value="#3b82f6" class="w-full h-10 border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Width (meters)</label>
                            <input type="number" id="pathWidth" value="2" step="0.5" class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Description</label>
                            <textarea id="pathDescription" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Describe this pathway..."></textarea>
                        </div>
                        <div class="text-xs text-gray-500 bg-gray-50 p-2 rounded">
                            <i class="fas fa-ruler-combined"></i> Length: <span id="pathLength">0</span> meters
                        </div>
                    </div>
                    <div class="flex gap-2 mt-6">
                        <button type="submit" class="flex-1 bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">Save Footwalk</button>
                        <button type="button" onclick="deleteCurrentPath()" class="flex-1 bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition">Delete</button>
                        <button type="button" onclick="closePathModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-400 transition">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let map;
        let markers = {};
        let drawnPaths = {};
        let connectionMarkers = [];
        let currentDrawControl = null;
        let isDrawing = false;
        let currentPathCoords = [];
        let footwalksData = [];
        let officesData = [];
        
        // Initialize map
        function initMap() {
            map = L.map('map').setView([9.853, 122.890], 17);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);
            
            map.on('click', function(e) {
                if (!isDrawing) {
                    selectedLatLng = e.latlng;
                    showOfficeModal();
                }
            });
            
            loadOffices();
            loadFootwalks();
            
            // Update connection distance display
            document.getElementById('connectionDistance').addEventListener('change', function() {
                document.getElementById('showConnectDist').innerText = this.value;
                recalculateConnections();
            });
        }
        
        // Toggle drawing mode
        function toggleDrawingMode() {
            isDrawing = !isDrawing;
            const drawBtn = document.getElementById('drawPathBtn');
            const drawStatus = document.getElementById('drawStatus');
            
            if (isDrawing) {
                drawBtn.classList.add('drawing-mode-active');
                drawBtn.innerHTML = '<i class="fas fa-stop mr-2"></i> Stop Drawing';
                drawStatus.innerHTML = '<i class="fas fa-draw-polygon mr-1"></i> Drawing: ACTIVE';
                drawStatus.classList.add('bg-green-100', 'text-green-700');
                enableDrawing();
                showToast('Drawing mode active - click on map to draw footwalk path', 'info');
            } else {
                drawBtn.classList.remove('drawing-mode-active');
                drawBtn.innerHTML = '<i class="fas fa-draw-polygon mr-2"></i> Draw New Footwalk';
                drawStatus.innerHTML = '<i class="fas fa-pencil-alt mr-1"></i> Drawing: OFF';
                drawStatus.classList.remove('bg-green-100', 'text-green-700');
                disableDrawing();
            }
        }
        
        function enableDrawing() {
            if (currentDrawControl) map.removeControl(currentDrawControl);
            
            currentDrawControl = new L.Draw.Polyline(map, {
                shapeOptions: { color: '#22c55e', weight: 5, opacity: 0.7 },
                showLength: true,
                metric: true,
                repeatMode: false
            });
            currentDrawControl.enable();
            
            map.on('draw:created', function(e) {
                const layer = e.layer;
                currentPathCoords = layer.getLatLngs().map(ll => [ll.lat, ll.lng]);
                
                // Calculate length for display
                let length = 0;
                for (let i = 0; i < currentPathCoords.length - 1; i++) {
                    length += calculateDistance(currentPathCoords[i][0], currentPathCoords[i][1], currentPathCoords[i+1][0], currentPathCoords[i+1][1]);
                }
                
                document.getElementById('pathLength').innerText = length.toFixed(2);
                
                document.getElementById('pathModalTitle').innerText = 'New Footwalk';
                document.getElementById('pathId').value = '';
                document.getElementById('pathName').value = '';
                document.getElementById('pathType').value = 'walkway';
                document.getElementById('pathColor').value = '#3b82f6';
                document.getElementById('pathWidth').value = '2';
                document.getElementById('pathDescription').value = '';
                document.getElementById('pathLength').innerText = length.toFixed(2);
                
                document.getElementById('pathModal').classList.remove('hidden');
                document.getElementById('pathModal').classList.add('flex');
                
                window.tempPathCoords = currentPathCoords;
                map.removeLayer(layer);
                disableDrawing();
                toggleDrawingMode();
            });
        }
        
        function disableDrawing() {
            if (currentDrawControl) {
                currentDrawControl.disable();
                map.removeControl(currentDrawControl);
                currentDrawControl = null;
            }
            map.off('draw:created');
        }
        
        function calculateDistance(lat1, lng1, lat2, lng2) {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLng = (lng2 - lng1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(lat1 * Math.PI/180) * Math.cos(lat2 * Math.PI/180) * Math.sin(dLng/2) * Math.sin(dLng/2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        }
        
        // Save footwalk
        document.getElementById('pathForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const coordinates = window.tempPathCoords || currentPathCoords;
            
            const data = {
                id: document.getElementById('pathId').value,
                name: document.getElementById('pathName').value,
                type: document.getElementById('pathType').value,
                color: document.getElementById('pathColor').value,
                width: parseFloat(document.getElementById('pathWidth').value),
                description: document.getElementById('pathDescription').value,
                coordinates: JSON.stringify(coordinates)
            };
            
            if (!data.name) {
                showToast('Please enter a path name', 'error');
                return;
            }
            
            try {
                const response = await fetch('/api/footwalks', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                if (result.success) {
                    showToast('Footwalk saved! Paths will auto-connect within ' + document.getElementById('connectionDistance').value + ' meters', 'success');
                    closePathModal();
                    location.reload();
                } else {
                    showToast('Error: ' + result.message, 'error');
                }
            } catch (error) {
                showToast('Error saving footwalk', 'error');
            }
        });
        
        // Load footwalks and find connections
        async function loadFootwalks() {
            try {
                const response = await fetch('/api/footwalks');
                const data = await response.json();
                if (data.success && data.data) {
                    footwalksData = data.data;
                    displayFootwalksList(data.data);
                    data.data.forEach(footwalk => drawFootwalk(footwalk));
                    findAndDisplayConnections();
                }
            } catch (error) {
                console.error('Error loading footwalks:', error);
            }
        }
        
        // Find connections between footwalks (mid-point intersections)
        function findAndDisplayConnections() {
            const connectionDistance = parseFloat(document.getElementById('connectionDistance').value);
            const connections = [];
            
            // Clear existing connection markers
            connectionMarkers.forEach(m => map.removeLayer(m));
            connectionMarkers = [];
            
            // Compare each pair of footwalks
            for (let i = 0; i < footwalksData.length; i++) {
                for (let j = i + 1; j < footwalksData.length; j++) {
                    const path1Coords = JSON.parse(footwalksData[i].coordinates);
                    const path2Coords = JSON.parse(footwalksData[j].coordinates);
                    
                    // Find closest points between the two paths
                    const closest = findClosestPointsBetweenPaths(path1Coords, path2Coords);
                    
                    if (closest.distance < connectionDistance) {
                        connections.push({
                            path1: footwalksData[i].name,
                            path2: footwalksData[j].name,
                            point: closest.point,
                            distance: closest.distance
                        });
                        
                        // Add connection marker at the intersection point
                        const connectionIcon = L.divIcon({
                            html: `<div style="background: #ff9800; width: 10px; height: 10px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 5px rgba(0,0,0,0.3);"></div>`,
                            iconSize: [14, 14]
                        });
                        const marker = L.marker([closest.point.lat, closest.point.lng], { icon: connectionIcon }).addTo(map);
                        marker.bindPopup(`<b>Connection Point</b><br>${footwalksData[i].name} ↔ ${footwalksData[j].name}<br>Distance: ${closest.distance.toFixed(1)}m`);
                        connectionMarkers.push(marker);
                    }
                }
            }
            
            // Update connections list display
            const container = document.getElementById('connectionsList');
            if (container) {
                container.innerHTML = connections.map(conn => `
                    <div class="bg-green-50 rounded-lg p-2 text-sm">
                        <i class="fas fa-link text-green-500 mr-2"></i>
                        <strong>${conn.path1}</strong> ↔ <strong>${conn.path2}</strong>
                        <span class="text-xs text-gray-500 block mt-1">Connected at distance: ${conn.distance.toFixed(1)} meters</span>
                    </div>
                `).join('');
                if (connections.length === 0) {
                    container.innerHTML = '<div class="text-center text-gray-500 text-sm p-4">No connections found. Draw paths that cross or come close to each other.</div>';
                }
            }
            
            document.getElementById('connectionCount').innerText = connections.length;
            return connections;
        }
        
        function findClosestPointsBetweenPaths(coords1, coords2) {
            let minDist = Infinity;
            let bestPoint = null;
            
            // Sample points along both paths
            for (let i = 0; i < coords1.length - 1; i++) {
                for (let j = 0; j < coords2.length - 1; j++) {
                    // Sample multiple points on each segment
                    for (let t1 = 0; t1 <= 10; t1++) {
                        const tVal1 = t1 / 10;
                        const lat1 = coords1[i][0] + tVal1 * (coords1[i+1][0] - coords1[i][0]);
                        const lng1 = coords1[i][1] + tVal1 * (coords1[i+1][1] - coords1[i][1]);
                        
                        for (let t2 = 0; t2 <= 10; t2++) {
                            const tVal2 = t2 / 10;
                            const lat2 = coords2[j][0] + tVal2 * (coords2[j+1][0] - coords2[j][0]);
                            const lng2 = coords2[j][1] + tVal2 * (coords2[j+1][1] - coords2[j][1]);
                            
                            const dist = calculateDistance(lat1, lng1, lat2, lng2);
                            if (dist < minDist) {
                                minDist = dist;
                                bestPoint = { lat: (lat1 + lat2) / 2, lng: (lng1 + lng2) / 2 };
                            }
                        }
                    }
                }
            }
            
            return { distance: minDist, point: bestPoint };
        }
        
        function recalculateConnections() {
            findAndDisplayConnections();
            showToast('Connections recalculated!', 'success');
        }
        
        function showAllConnections() {
            if (connectionMarkers.length === 0) {
                showToast('No connections found. Draw paths that cross or come within 5 meters.', 'info');
            } else {
                const bounds = L.latLngBounds();
                connectionMarkers.forEach(m => bounds.extend(m.getLatLng()));
                map.fitBounds(bounds);
                showToast(`Showing ${connectionMarkers.length} connection points`, 'success');
            }
        }
        
        function drawFootwalk(footwalk) {
            try {
                const coords = JSON.parse(footwalk.coordinates);
                const latlngs = coords.map(c => [c[0], c[1]]);
                const line = L.polyline(latlngs, {
                    color: footwalk.color || '#3b82f6',
                    weight: footwalk.width * 2 || 4,
                    opacity: 0.8,
                    smoothFactor: 1
                }).addTo(map);
                
                line.bindPopup(`
                    <div class="p-2 min-w-[200px]">
                        <strong>${footwalk.name}</strong><br>
                        <span class="text-xs">Type: ${footwalk.type}</span><br>
                        <span class="text-xs">Width: ${footwalk.width}m</span><br>
                        <div class="flex gap-2 mt-2">
                            <button onclick="editFootwalk('${footwalk.id}')" class="bg-blue-500 text-white px-2 py-1 rounded text-xs">Edit</button>
                            <button onclick="deleteFootwalkItem('${footwalk.id}')" class="bg-red-500 text-white px-2 py-1 rounded text-xs">Delete</button>
                        </div>
                    </div>
                `);
                drawnPaths[footwalk.id] = line;
            } catch (e) {}
        }
        
        function displayFootwalksList(footwalks) {
            const container = document.getElementById('pathsList');
            container.innerHTML = footwalks.map(fw => `
                <div class="bg-gray-50 rounded-lg p-3 hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center"><div class="w-3 h-3 rounded-full mr-2" style="background-color: ${fw.color || '#3b82f6'}"></div><h4 class="font-semibold text-sm">${fw.name}</h4></div>
                            <p class="text-xs text-gray-500 mt-1"><i class="fas fa-road mr-1"></i> ${fw.type} | ${fw.width}m wide</p>
                        </div>
                        <div class="flex gap-1"><button onclick="editFootwalk('${fw.id}')" class="text-blue-600"><i class="fas fa-edit text-sm"></i></button><button onclick="deleteFootwalkItem('${fw.id}')" class="text-red-600"><i class="fas fa-trash text-sm"></i></button></div>
                    </div>
                </div>
            `).join('');
            
            document.getElementById('searchPath').addEventListener('keyup', function(e) {
                const term = e.target.value.toLowerCase();
                const filtered = footwalks.filter(f => f.name.toLowerCase().includes(term));
                displayFootwalksList(filtered);
            });
        }
        
        async function editFootwalk(id) {
            try {
                const response = await fetch(`/api/footwalks/${id}`);
                const data = await response.json();
                if (data.success) {
                    const fw = data.data;
                    document.getElementById('pathId').value = fw.id;
                    document.getElementById('pathName').value = fw.name;
                    document.getElementById('pathType').value = fw.type;
                    document.getElementById('pathColor').value = fw.color || '#3b82f6';
                    document.getElementById('pathWidth').value = fw.width;
                    document.getElementById('pathDescription').value = fw.description || '';
                    const coords = JSON.parse(fw.coordinates);
                    let length = 0;
                    for (let i = 0; i < coords.length - 1; i++) length += calculateDistance(coords[i][0], coords[i][1], coords[i+1][0], coords[i+1][1]);
                    document.getElementById('pathLength').innerText = length.toFixed(2);
                    window.tempPathCoords = coords;
                    document.getElementById('pathModalTitle').innerText = 'Edit Footwalk';
                    document.getElementById('pathModal').classList.remove('hidden');
                    document.getElementById('pathModal').classList.add('flex');
                }
            } catch (error) { showToast('Error loading footwalk', 'error'); }
        }
        
        function deleteFootwalkItem(id) { if (confirm('Delete this footwalk?')) deleteFootwalk(id); }
        async function deleteFootwalk(id) {
            await fetch(`/api/footwalks/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
            showToast('Footwalk deleted', 'success');
            location.reload();
        }
        function deleteCurrentPath() { const id = document.getElementById('pathId').value; if (id && confirm('Delete?')) deleteFootwalk(id); closePathModal(); }
        
        // Office functions
        async function loadOffices() {
            try {
                const response = await fetch('/api/offices');
                const data = await response.json();
                if (data.success && data.data) {
                    officesData = data.data;
                    displayOfficesList(data.data);
                    data.data.forEach(office => addMarkerToMap(office));
                }
            } catch (error) { console.error('Error loading offices:', error); }
        }
        
        function addMarkerToMap(office) {
            if (!office.lat || !office.lng) return;
            let color = office.category === 'Administrative' ? '#ef4444' : office.category === 'Academic' ? '#3b82f6' : '#10b981';
            const icon = L.divIcon({ html: `<div style="background:${color};width:12px;height:12px;border-radius:50%;border:2px solid white;box-shadow:0 2px 5px rgba(0,0,0,0.3);"></div>`, iconSize: [16,16] });
            const marker = L.marker([office.lat, office.lng], { icon }).addTo(map);
            marker.bindPopup(`<b>${office.name}</b><br>${office.building}<br><button onclick="editOffice('${office.office_id}')" class="bg-blue-500 text-white px-2 py-1 rounded text-xs">Edit</button> <button onclick="deleteOfficeItem('${office.office_id}')" class="bg-red-500 text-white px-2 py-1 rounded text-xs">Delete</button>`);
            markers[office.office_id] = marker;
        }
        
        function displayOfficesList(offices) {
            const container = document.getElementById('officesList');
            container.innerHTML = offices.map(office => `
                <div class="bg-gray-50 rounded-lg p-3"><div class="flex justify-between"><div><h4 class="font-semibold text-sm">${office.name}</h4><p class="text-xs text-gray-500">${office.building}</p></div><div><button onclick="editOffice('${office.office_id}')" class="text-blue-600 mr-2"><i class="fas fa-edit"></i></button><button onclick="deleteOfficeItem('${office.office_id}')" class="text-red-600"><i class="fas fa-trash"></i></button></div></div></div>
            `).join('');
            document.getElementById('searchOffice').addEventListener('keyup', function(e) {
                const term = e.target.value.toLowerCase();
                const filtered = offices.filter(o => o.name.toLowerCase().includes(term));
                displayOfficesList(filtered);
            });
        }
        
        document.getElementById('officeForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = {
                office_id: document.getElementById('officeId').value,
                name: document.getElementById('officeName').value,
                building: document.getElementById('officeBuilding').value,
                room_number: document.getElementById('officeRoom').value,
                category: document.getElementById('officeCategory').value,
                working_hours: document.getElementById('officeHours').value,
                contact_number: document.getElementById('officeContact').value,
                lat: document.getElementById('officeLat').value,
                lng: document.getElementById('officeLng').value
            };
            if (!data.lat || !data.lng) { showToast('Click on map to set location', 'error'); return; }
            try {
                const response = await fetch('/api/offices', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                if (result.success) { showToast('Office saved!', 'success'); closeOfficeModal(); location.reload(); }
                else showToast('Error: ' + result.message, 'error');
            } catch (error) { showToast('Error saving office', 'error'); }
        });
        
        async function editOffice(officeId) {
            const office = officesData.find(o => o.office_id === officeId);
            if (office) {
                document.getElementById('officeId').value = office.office_id;
                document.getElementById('officeName').value = office.name;
                document.getElementById('officeBuilding').value = office.building;
                document.getElementById('officeRoom').value = office.room_number || '';
                document.getElementById('officeCategory').value = office.category;
                document.getElementById('officeHours').value = office.working_hours || '';
                document.getElementById('officeContact').value = office.contact_number || '';
                document.getElementById('officeLat').value = office.lat;
                document.getElementById('officeLng').value = office.lng;
                document.getElementById('officeModalTitle').innerText = 'Edit Office';
                showOfficeModal();
            }
        }
        
        function deleteOfficeItem(id) { if (confirm('Delete this office?')) deleteOffice(id); }
        async function deleteOffice(id) {
            await fetch(`/api/offices/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
            showToast('Office deleted', 'success');
            location.reload();
        }
        
        let selectedLatLng = null;
        function showOfficeModal() {
            if (selectedLatLng) {
                document.getElementById('officeLat').value = selectedLatLng.lat.toFixed(8);
                document.getElementById('officeLng').value = selectedLatLng.lng.toFixed(8);
            }
            document.getElementById('officeModal').classList.remove('hidden');
            document.getElementById('officeModal').classList.add('flex');
        }
        function closeOfficeModal() {
            document.getElementById('officeModal').classList.add('hidden');
            document.getElementById('officeForm').reset();
            document.getElementById('officeId').value = '';
            document.getElementById('officeModalTitle').innerText = 'Add New Office';
            selectedLatLng = null;
        }
        function closePathModal() {
            document.getElementById('pathModal').classList.add('hidden');
            window.tempPathCoords = null;
        }
        function resetMapView() { map.setView([9.853, 122.890], 17); }
        
        function switchTab(tab) {
            document.getElementById('officesTab').classList.add('hidden');
            document.getElementById('pathsTab').classList.add('hidden');
            document.getElementById('connectionsTab').classList.add('hidden');
            if (tab === 'offices') {
                document.getElementById('officesTab').classList.remove('hidden');
                document.getElementById('tabOfficesBtn').classList.add('tab-active');
                document.getElementById('tabPathsBtn').classList.remove('tab-active');
                document.getElementById('tabConnectionsBtn').classList.remove('tab-active');
            } else if (tab === 'paths') {
                document.getElementById('pathsTab').classList.remove('hidden');
                document.getElementById('tabPathsBtn').classList.add('tab-active');
                document.getElementById('tabOfficesBtn').classList.remove('tab-active');
                document.getElementById('tabConnectionsBtn').classList.remove('tab-active');
            } else {
                document.getElementById('connectionsTab').classList.remove('hidden');
                document.getElementById('tabConnectionsBtn').classList.add('tab-active');
                document.getElementById('tabOfficesBtn').classList.remove('tab-active');
                document.getElementById('tabPathsBtn').classList.remove('tab-active');
                findAndDisplayConnections();
            }
        }
        
        function showToast(message, type) {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>${message}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
        
        document.addEventListener('DOMContentLoaded', initMap);
    </script>
</body>
</html>