<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>CPSU Campus Navigator</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f1f5f9; overflow: hidden; height: 100vh; }

    /* Header */
    .app-header { background: #0057a3; color: white; display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; height: 56px; position: fixed; top: 0; left: 0; right: 0; z-index: 100; }
    .header-logo { background: white; color: #0057a3; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
    .header-title { font-weight: 600; font-size: 0.9rem; text-align: center; flex: 1; }
    .header-title span { font-size: 0.65rem; opacity: 0.85; display: block; }
    .gps-btn { background: rgba(255,255,255,0.2); border: none; border-radius: 25px; padding: 6px 12px; color: white; font-size: 0.7rem; cursor: pointer; }

    /* Layout with Sidebar */
    .app-body { display: flex; position: fixed; top: 56px; bottom: 0; left: 0; right: 0; }
    
    /* Sidebar (Left Panel) */
    .sidebar { width: 280px; background: white; border-right: 1px solid #e9edf2; display: flex; flex-direction: column; overflow-y: auto; flex-shrink: 0; z-index: 10; }
    .sidebar-header { padding: 16px; border-bottom: 1px solid #e9edf2; }
    .sidebar-label { font-weight: 600; font-size: 0.7rem; text-transform: uppercase; color: #5c6f87; margin-bottom: 10px; }
    .search-box { display: flex; align-items: center; background: #f1f4f9; border-radius: 40px; padding: 8px 14px; gap: 8px; }
    .search-icon { font-size: 1rem; color: #7e8b9e; }
    #searchInput { border: none; background: transparent; width: 100%; font-size: 0.85rem; outline: none; }
    
    .nav-category { margin-top: 16px; }
    .category-title { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; padding: 0 16px 6px; color: #5c6f87; }
    .sidebar-nav-item { display: flex; align-items: center; gap: 12px; padding: 10px 16px; margin: 2px 8px; border-radius: 12px; font-weight: 500; font-size: 0.85rem; color: #1a2c3e; cursor: pointer; transition: all 0.2s; }
    .sidebar-nav-item:hover { background: #eef2f9; }
    .sidebar-nav-item.active { background: #e0f2ea; color: #1D9E75; border-left: 3px solid #1D9E75; }
    .nav-icon { font-size: 1.2rem; width: 28px; }
    
    .sidebar-footer { margin-top: auto; padding: 16px; font-size: 0.65rem; border-top: 1px solid #e9edf2; color: #6a7b8c; }

    /* Map Area */
    .map-area { flex: 1; position: relative; }
    #map { height: 100%; width: 100%; }
    
    /* Map Controls */
    .map-controls { position: absolute; bottom: 20px; right: 10px; background: white; border-radius: 40px; display: flex; flex-direction: column; gap: 4px; padding: 6px; z-index: 150; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
    .map-ctrl-btn { width: 40px; height: 40px; background: white; display: flex; align-items: center; justify-content: center; border-radius: 50%; cursor: pointer; font-size: 1.2rem; }
    .my-location-btn { position: absolute; bottom: 20px; left: 10px; width: 40px; height: 40px; background: #e74c3c; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 150; border: none; font-size: 1.2rem; }
    .gps-status { position: absolute; bottom: 20px; left: 60px; background: rgba(0,0,0,0.7); color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.6rem; z-index: 150; }

    /* Right Panel (Content) */
    .right-panel { width: 300px; background: white; border-left: 1px solid #e9edf2; display: flex; flex-direction: column; overflow-y: auto; flex-shrink: 0; }
    .panel-header { padding: 16px; border-bottom: 1px solid #e9edf2; }
    .panel-header h3 { font-size: 0.9rem; }
    .content-list { flex: 1; overflow-y: auto; padding: 12px; }
    
    .location-item { padding: 12px; border-bottom: 1px solid #eee; cursor: pointer; border-radius: 12px; margin-bottom: 4px; }
    .location-item:hover { background: #eef2f9; }
    .location-name { font-weight: 600; font-size: 0.9rem; }
    .location-building { font-size: 0.7rem; color: #5c6f87; margin-top: 4px; }
    .location-dist { font-size: 0.65rem; color: #1D9E75; margin-top: 4px; }
    
    .step-item { padding: 12px; border-left: 3px solid #ccc; margin-bottom: 8px; background: #f9fafc; border-radius: 10px; cursor: pointer; }
    .step-item.completed { border-left-color: #1D9E75; background: #f6fef9; }
    
    /* Route Panel */
    .route-panel { position: absolute; bottom: 80px; left: 10px; right: 10px; background: white; border-radius: 16px; padding: 12px; z-index: 200; display: none; box-shadow: 0 2px 10px rgba(0,0,0,0.2); }
    .route-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.8rem; }
    .save-badge { background: #fef5e0; color: #e67e22; padding: 4px 8px; border-radius: 20px; font-size: 0.7rem; }
    .nav-btn { width: 100%; padding: 10px; background: #0057a3; color: white; border: none; border-radius: 25px; margin-top: 8px; cursor: pointer; }
    .close-route { position: absolute; top: 8px; right: 12px; background: none; border: none; font-size: 1.2rem; cursor: pointer; }

    .loading { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 300; display: none; }
    .spinner { width: 40px; height: 40px; border: 3px solid white; border-top-color: #1D9E75; border-radius: 50%; animation: spin 1s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }

    @media (max-width: 768px) {
      .sidebar { position: fixed; left: -280px; top: 56px; bottom: 0; z-index: 200; transition: left 0.3s ease; }
      .sidebar.open { left: 0; }
      .right-panel { position: fixed; right: -300px; top: 56px; bottom: 0; z-index: 200; transition: right 0.3s ease; }
      .right-panel.open { right: 0; }
      .menu-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 199; display: none; }
      .menu-overlay.show { display: block; }
      .mobile-menu-btn { display: block; background: none; border: none; color: white; font-size: 1.3rem; cursor: pointer; margin-right: 12px; }
    }
    @media (min-width: 769px) { .mobile-menu-btn { display: none; } .menu-overlay { display: none; } }
    .mobile-menu-btn { background: none; border: none; color: white; font-size: 1.3rem; cursor: pointer; }
  </style>
</head>
<body>
  <div class="app-header">
    <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>
    <div class="header-logo">CPSU</div>
    <div class="header-title">Campus Navigator<span>Kabankalan City</span></div>
    <button class="gps-btn" onclick="startGPS()">📍 Start</button>
  </div>

  <div class="app-body">
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
      <div class="sidebar-header">
        <div class="sidebar-label">Search</div>
        <div class="search-box">
          <span class="search-icon">🔍</span>
          <input type="text" id="searchInput" placeholder="Search offices..." />
        </div>
      </div>
      <div class="nav-category">
        <div class="category-title">MAIN</div>
        <div class="sidebar-nav-item active" data-view="map"><span class="nav-icon">🗺️</span> Campus Map</div>
        <div class="sidebar-nav-item" data-view="processes"><span class="nav-icon">📋</span> Processes</div>
        <div class="sidebar-nav-item" data-view="help"><span class="nav-icon">❓</span> Help</div>
      </div>
      <div class="sidebar-footer">
        <span>📍 Kabankalan City, Negros Occidental</span><br/>
        <span id="gpsFooterStatus">⚪ GPS: Not started</span>
      </div>
    </div>

    <!-- Map Area -->
    <div class="map-area">
      <div id="map"></div>
      <div class="map-controls"><div class="map-ctrl-btn" onclick="if(map) map.zoomIn()">＋</div><div class="map-ctrl-btn" onclick="if(map) map.zoomOut()">－</div></div>
      <button class="my-location-btn" onclick="centerOnUser()">📍</button>
      <div class="gps-status" id="gpsStatus">📍 GPS: Off</div>
      <div class="route-panel" id="routePanel"><button class="close-route" onclick="closeRoutePanel()">✕</button><div class="route-row"><strong id="destName">-</strong></div><div class="route-row"><span>🚶 Walkway Path</span><span id="pathInfo">-</span></div><div class="route-row"><span>⚡ Shortcut</span><span id="shortcutInfo">-</span></div><div class="route-row"><span class="save-badge" id="saveInfo">-</span></div><button class="nav-btn" onclick="startNavigation()">🚶 Start Navigation</button></div>
    </div>

    <!-- Right Panel (Content) -->
    <div class="right-panel" id="rightPanel">
      <div class="panel-header"><h3 id="panelTitle">📍 Campus Locations</h3></div>
      <div class="content-list" id="contentList"><div style="text-align:center; padding:20px;">Loading...</div></div>
    </div>
  </div>

  <div class="menu-overlay" id="menuOverlay" onclick="closeSidebar()"></div>
  <div class="loading" id="loading"><div class="spinner"></div></div>

  <script>
    // Use Laravel's URL helper for API endpoints
    const API_URL = '{{ url("/api") }}';
    
    let map, userMarker, destMarker, routeLine, shortcutLine, currentDestination = null;
    let userLocation = { lat: 9.85325, lng: 122.88981 };
    let watchId = null;
    let offices = [];
    let processes = [];
    let completedSteps = JSON.parse(localStorage.getItem('completedSteps') || '{}');
    let currentView = 'map';

    function showLoading(show) { document.getElementById("loading").style.display = show ? "flex" : "none"; }
    function saveProgress() { localStorage.setItem('completedSteps', JSON.stringify(completedSteps)); }
    function toggleSidebar() { document.getElementById("sidebar").classList.toggle("open"); document.getElementById("menuOverlay").classList.toggle("show"); }
    function closeSidebar() { document.getElementById("sidebar").classList.remove("open"); document.getElementById("menuOverlay").classList.remove("show"); }

    async function fetchOffices() {
      try { 
        const res = await fetch(`${API_URL}/offices`); 
        const data = await res.json(); 
        offices = data.data || data; 
        addMarkersToMap(); 
        updateLocationList(); 
        return offices; 
      } 
      catch(e) { 
        console.error("API error:", e); 
        return []; 
      }
    }
    
    async function fetchProcesses() {
      try { 
        const res = await fetch(`${API_URL}/processes`); 
        const data = await res.json(); 
        processes = data.data || data; 
        return processes; 
      } 
      catch(e) { 
        console.error("API error:", e); 
        return []; 
      }
    }

    function addMarkersToMap() {
      if (!map) return;
      const colors = { academic: '#1a5f3a', admin: '#d4a017', facility: '#e67e22', housing: '#3498db' };
      offices.forEach(office => {
        const color = colors[office.category] || '#666';
        const icon = L.divIcon({ 
          html: `<div style="background: ${color}; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 14px; border: 2px solid white;">📍</div>`, 
          iconSize: [32, 32] 
        });
        const marker = L.marker([office.lat, office.lng], { icon }).addTo(map);
        marker.bindPopup(`
          <b>${office.name}</b><br>
          🏢 ${office.building || 'Main Building'}<br>
          🕒 ${office.hours || '8AM-5PM'}<br>
          <button onclick="selectDestination('${office.id}')" style="margin-top:5px; padding:4px 12px; background:#0057a3; color:white; border:none; border-radius:5px; cursor:pointer;">Navigate</button>
        `);
      });
    }

    function initMap() {
      map = L.map('map').setView([userLocation.lat, userLocation.lng], 17);
      L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', { 
        maxZoom: 20,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>'
      }).addTo(map);
      addMarkersToMap();
      updateUserMarker();
    }

    function calculateDistance(lat1, lng1, lat2, lng2) {
      const R = 6371e3;
      const φ1 = lat1 * Math.PI/180, φ2 = lat2 * Math.PI/180;
      const Δφ = (lat2-lat1) * Math.PI/180, Δλ = (lng2-lng1) * Math.PI/180;
      const a = Math.sin(Δφ/2)*Math.sin(Δφ/2) + Math.cos(φ1)*Math.cos(φ2)*Math.sin(Δλ/2)*Math.sin(Δλ/2);
      return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }

    function updateUserMarker() {
      if (userMarker) map.removeLayer(userMarker);
      const userIcon = L.divIcon({ 
        html: `<div style="background: #e74c3c; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 5px rgba(0,0,0,0.3);"></div>`, 
        iconSize: [20, 20] 
      });
      userMarker = L.marker([userLocation.lat, userLocation.lng], { icon: userIcon }).addTo(map);
      map.setView([userLocation.lat, userLocation.lng], 17);
    }

    function startGPS() {
      if (!navigator.geolocation) { alert("Geolocation not supported"); return; }
      if (watchId) navigator.geolocation.clearWatch(watchId);
      
      navigator.geolocation.getCurrentPosition(
        (pos) => {
          userLocation = { lat: pos.coords.latitude, lng: pos.coords.longitude };
          updateUserMarker();
          document.getElementById("gpsStatus").innerHTML = "🟢 GPS: Active";
          document.getElementById("gpsStatus").style.background = "#27ae60";
          document.getElementById("gpsFooterStatus").innerHTML = "🟢 GPS: Active";
          updateLocationList();
          
          watchId = navigator.geolocation.watchPosition(
            (newPos) => { 
              userLocation = { lat: newPos.coords.latitude, lng: newPos.coords.longitude }; 
              updateUserMarker(); 
              if (currentDestination) drawRoutes(); 
              updateLocationList(); 
            },
            (e) => console.error(e),
            { enableHighAccuracy: true, maximumAge: 5000, timeout: 15000 }
          );
        },
        (err) => { 
          let msg = "GPS: Denied";
          if(err.code === 1) msg = "⚠️ GPS: Permission denied";
          if(err.code === 2) msg = "⚠️ GPS: Position unavailable";
          document.getElementById("gpsStatus").innerHTML = msg;
          document.getElementById("gpsStatus").style.background = "#e74c3c";
          document.getElementById("gpsFooterStatus").innerHTML = msg;
          alert("Please enable location access for better navigation experience.");
        },
        { enableHighAccuracy: true, timeout: 10000 }
      );
    }

    function centerOnUser() { 
      map.setView([userLocation.lat, userLocation.lng], 17); 
    }

    function drawRoutes() {
      if (!currentDestination) return;
      if (routeLine) map.removeLayer(routeLine);
      if (shortcutLine) map.removeLayer(shortcutLine);
      
      const dist = calculateDistance(userLocation.lat, userLocation.lng, currentDestination.lat, currentDestination.lng);
      const pathM = (dist * 1.15).toFixed(0);
      const directM = dist.toFixed(0);
      const pathT = Math.round(dist * 1.15 / 80);
      const directT = Math.round(dist / 80);
      const saved = pathT - directT;
      
      routeLine = L.polyline([[userLocation.lat, userLocation.lng], [currentDestination.lat, currentDestination.lng]], 
        { color: "#0057a3", weight: 5, opacity: 0.8 }).addTo(map);
      shortcutLine = L.polyline([[userLocation.lat, userLocation.lng], [currentDestination.lat, currentDestination.lng]], 
        { color: "#e67e22", weight: 4, dashArray: "10, 10", opacity: 0.8 }).addTo(map);
      
      document.getElementById("routePanel").style.display = "block";
      document.getElementById("destName").innerHTML = `🎯 ${currentDestination.name}`;
      document.getElementById("pathInfo").innerHTML = `${pathM}m • ${pathT}min`;
      document.getElementById("shortcutInfo").innerHTML = `${directM}m • ${directT}min`;
      document.getElementById("saveInfo").innerHTML = `💪 Save ${saved} min`;
      
      map.fitBounds([[userLocation.lat, userLocation.lng], [currentDestination.lat, currentDestination.lng]], { padding: [50, 50] });
    }

    function selectDestination(id) {
      currentDestination = offices.find(o => o.id == id);
      if (!currentDestination) return;
      
      if (destMarker) map.removeLayer(destMarker);
      destMarker = L.marker([currentDestination.lat, currentDestination.lng], { 
        icon: L.divIcon({ 
          html: `<div style="background:#e74c3c; width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-size:14px; border:2px solid white;">🎯</div>`, 
          iconSize: [28,28] 
        }) 
      }).addTo(map);
      
      drawRoutes();
      closeSidebar();
    }

    function startNavigation() { 
      if(currentDestination) {
        alert(`🚶 Navigating to ${currentDestination.name}\n\n📍 Follow BLUE line (walkway)\n⚡ Take ORANGE shortcut to save time!`);
        // Fit bounds to show the full route
        map.fitBounds([[userLocation.lat, userLocation.lng], [currentDestination.lat, currentDestination.lng]], { padding: [50, 50] });
      }
    }
    
    function closeRoutePanel() { 
      document.getElementById("routePanel").style.display = "none"; 
      if(routeLine) map.removeLayer(routeLine); 
      if(shortcutLine) map.removeLayer(shortcutLine); 
      if(destMarker) map.removeLayer(destMarker); 
      currentDestination = null; 
    }

    function updateLocationList() {
      const container = document.getElementById("contentList");
      if (!container || !offices.length) return;
      
      container.innerHTML = offices.map(office => {
        const dist = calculateDistance(userLocation.lat, userLocation.lng, office.lat, office.lng);
        return `<div class="location-item" onclick="selectDestination(${office.id})">
                  <div class="location-name">📍 ${office.name}</div>
                  <div class="location-building">🏢 ${office.building || 'Main Building'}</div>
                  <div class="location-dist">📏 ${dist.toFixed(0)} meters away</div>
                </div>`;
      }).join('');
    }

    function showProcesses() {
      currentView = 'processes';
      document.getElementById("panelTitle").innerHTML = "📋 Processes";
      const container = document.getElementById("contentList");
      if (!processes.length) { 
        container.innerHTML = '<div style="padding:20px;text-align:center;">Loading processes...</div>'; 
        return; 
      }
      container.innerHTML = processes.map(p => {
        const completed = completedSteps[p.id]?.length || 0;
        const stepCount = p.steps ? p.steps.length : 0;
        const progressPercent = stepCount > 0 ? (completed/stepCount)*100 : 0;
        return `<div class="location-item" onclick="showProcessSteps(${p.id})">
                  <div class="location-name">📌 ${p.name}</div>
                  <div class="location-building">Progress: ${completed}/${stepCount}</div>
                  <div style="background:#ddd; height:4px; border-radius:2px; margin-top:8px;">
                    <div style="background:#1D9E75; width:${progressPercent}%; height:4px; border-radius:2px;"></div>
                  </div>
                </div>`;
      }).join('');
    }

    function showProcessSteps(processId) {
      const p = processes.find(p => p.id === processId);
      if (!p) return;
      
      const container = document.getElementById("contentList");
      container.innerHTML = `<div style="margin-bottom:12px;">
          <button onclick="showProcesses()" style="background:none; border:none; color:#0057a3; font-size:0.8rem; cursor:pointer;">← Back to Processes</button>
        </div>
        <h4>${p.name}</h4>
        ${p.steps ? p.steps.map((step, idx) => {
          const office = offices.find(o => o.id == step);
          if(!office) return '';
          const dist = calculateDistance(userLocation.lat, userLocation.lng, office.lat, office.lng);
          const isCompleted = completedSteps[processId]?.includes(step);
          return `<div class="step-item ${isCompleted ? 'completed' : ''}" onclick="if(!${isCompleted}) selectDestination(${step})">
                    <div><strong>${idx+1}. ${office.name}</strong></div>
                    <div style="font-size:0.7rem;">🏢 ${office.building || 'Main Building'}</div>
                    <div style="font-size:0.65rem;">📏 ${dist.toFixed(0)}m away</div>
                    ${!isCompleted ? `<button onclick="event.stopPropagation(); completeStep(${processId}, ${step})" style="margin-top:6px; padding:4px 8px; background:#1D9E75; color:white; border:none; border-radius:16px; font-size:0.6rem; cursor:pointer;">✓ Mark Completed</button>` : '<span style="color:#1D9E75;">✓ Completed</span>'}
                  </div>`;
        }).join('') : '<p>No steps defined</p>'}
      `;
    }

    function completeStep(processId, stepId) {
      if (!completedSteps[processId]) completedSteps[processId] = [];
      if (!completedSteps[processId].includes(stepId)) {
        completedSteps[processId].push(stepId);
        saveProgress();
        alert(`✅ Step completed!`);
        showProcessSteps(processId);
      }
    }

    function showHelp() {
      currentView = 'help';
      document.getElementById("panelTitle").innerHTML = "❓ Help Center";
      document.getElementById("contentList").innerHTML = `
        <div style="padding:8px;">
          <h4>📱 How to Use CPSU Campus Navigator</h4>
          <p style="margin:12px 0;"><strong>1.</strong> Tap <strong>"Start"</strong> and allow location access</p>
          <p style="margin:12px 0;"><strong>2.</strong> Your <span style="color:#e74c3c;">red dot</span> shows your current position</p>
          <p style="margin:12px 0;"><strong>3.</strong> Tap any office to see route and distance</p>
          <p style="margin:12px 0;"><strong>4.</strong> <span style="color:#0057a3;">Blue line</span> = Walkway path</p>
          <p style="margin:12px 0;"><strong>5.</strong> <span style="color:#e67e22;">Orange dashed</span> = Shortcut</p>
          <p style="margin:12px 0;"><strong>6.</strong> Use Processes tab to track your progress</p>
          <hr style="margin:16px 0;">
          <p><strong>📍 Central Philippines State University</strong><br>Kabankalan City, Negros Occidental</p>
          <p style="margin-top:12px; font-size:0.7rem; color:#666;">For assistance, contact the IT Office</p>
        </div>
      `;
    }

    // Event Listeners
    document.querySelectorAll(".sidebar-nav-item").forEach(item => {
      item.addEventListener("click", () => {
        document.querySelectorAll(".sidebar-nav-item").forEach(nav => nav.classList.remove("active"));
        item.classList.add("active");
        const view = item.getAttribute("data-view");
        if (view === "map") { 
          updateLocationList(); 
          document.getElementById("panelTitle").innerHTML = "📍 Campus Locations";
          currentView = 'map';
        }
        else if (view === "processes") showProcesses();
        else if (view === "help") showHelp();
        closeSidebar();
      });
    });

    document.getElementById("searchInput").addEventListener("input", (e) => {
      const term = e.target.value.toLowerCase();
      const items = document.querySelectorAll(".location-item");
      items.forEach(el => {
        const text = el.innerText.toLowerCase();
        el.style.display = text.includes(term) ? "block" : "none";
      });
    });

    // Initialize the app
    async function init() {
      showLoading(true);
      await fetchOffices();
      await fetchProcesses();
      initMap();
      updateLocationList();
      showLoading(false);
    }

    init();
    
    // Make functions globally available
    window.startGPS = startGPS;
    window.centerOnUser = centerOnUser;
    window.selectDestination = selectDestination;
    window.startNavigation = startNavigation;
    window.closeRoutePanel = closeRoutePanel;
    window.toggleSidebar = toggleSidebar;
    window.completeStep = completeStep;
    window.showProcessSteps = showProcessSteps;
    window.showProcesses = showProcesses;
    window.updateLocationList = updateLocationList;
    window.showHelp = showHelp;
  </script>
</body>
</html>