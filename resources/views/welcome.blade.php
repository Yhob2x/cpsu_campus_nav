<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- PWA / Mobile App Support -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    
    <!-- Status Bar -->
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- Theme -->
    <meta name="theme-color" content="#0f172a">

    <title>CPSU Map Navigator</title>
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        *{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
        :root{--sat:env(safe-area-inset-top,0px);--sab:env(safe-area-inset-bottom,0px)}
        html,body{width:100%;height:100%;overflow:hidden;position:fixed;top:0;left:0;font-family:'Inter',sans-serif;background:#000}
        #map{position:fixed;top:0;left:0;width:100%;height:100%;z-index:1}

        .header{position:fixed;top:0;left:0;right:0;z-index:50;padding:8px 10px;padding-top:calc(8px + var(--sat));display:flex;align-items:center;gap:8px;pointer-events:none}
        .header>*{pointer-events:auto}
        .header-brand{display:flex;align-items:center;gap:6px;background:rgba(15,23,42,0.88);backdrop-filter:blur(16px);padding:6px 12px;border-radius:22px;border:1px solid rgba(255,255,255,0.1)}
        .header-logo{font-size:1.1rem}.header-title{color:white;font-weight:700;font-size:0.8rem;white-space:nowrap}.header-spacer{flex:1}
        .live-badge{display:none;align-items:center;gap:5px;background:rgba(16,185,129,0.25);color:#6ee7b7;padding:5px 12px;border-radius:18px;font-size:0.65rem;font-weight:700;border:1px solid rgba(16,185,129,0.3);animation:livePulse 2s infinite}
        .live-badge.show{display:flex}.live-dot{width:6px;height:6px;background:#10b981;border-radius:50%;animation:dotPulse 1.5s infinite}
        @keyframes livePulse{0%,100%{box-shadow:0 0 0 0 rgba(16,185,129,0.4)}50%{box-shadow:0 0 0 8px rgba(16,185,129,0)}}@keyframes dotPulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.5);opacity:0.4}}
        .header-btns{display:flex;gap:5px}
        .icon-btn{width:32px;height:32px;border-radius:50%;background:rgba(15,23,42,0.88);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,0.1);color:white;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:0.8rem;transition:all 0.15s;text-decoration:none}
        .icon-btn:active{transform:scale(0.9);background:rgba(255,255,255,0.2)}

        .search-bar{position:fixed;top:calc(52px + var(--sat));left:50%;transform:translateX(-50%);z-index:45;pointer-events:none;width:calc(100% - 24px);max-width:420px}
        .search-bar>*{pointer-events:auto}
        .search-wrapper{position:relative;background:rgba(255,255,255,0.95);backdrop-filter:blur(16px);border-radius:24px;box-shadow:0 4px 20px rgba(0,0,0,0.15);border:1px solid rgba(255,255,255,0.5)}
        #searchInput{width:100%;padding:10px 40px;border:none;border-radius:24px;font-size:0.85rem;background:transparent;font-weight:500;color:#0f172a;outline:none}
        #searchInput::placeholder{color:#94a3b8;font-size:0.8rem}
        .search-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:0.85rem}
        .search-clear{position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;color:#94a3b8;padding:6px;cursor:pointer;display:none;font-size:0.8rem}
        .search-clear.visible{display:block}
        .search-suggestions{position:absolute;top:46px;left:0;right:0;background:white;border-radius:14px;box-shadow:0 8px 28px rgba(0,0,0,0.15);max-height:200px;overflow-y:auto;display:none}
        .search-suggestions.show{display:block}
        .suggestion-item{padding:10px 14px;cursor:pointer;display:flex;align-items:center;gap:8px;border-bottom:1px solid #f1f5f9;font-size:0.8rem;transition:background 0.1s}
        .suggestion-item:last-child{border-bottom:none}.suggestion-item:active{background:#f8fafc}
        .suggestion-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}.suggestion-info{flex:1;min-width:0}
        .suggestion-name{font-weight:600;color:#0f172a;font-size:0.8rem}.suggestion-detail{font-size:0.7rem;color:#64748b}

        .bottom-controls{position:fixed;bottom:16px;bottom:calc(16px + var(--sab));left:50%;transform:translateX(-50%);z-index:45;display:flex;gap:6px;pointer-events:none}
        .bottom-controls>*{pointer-events:auto}
        .pill-btn{padding:8px 14px;border-radius:20px;border:none;font-weight:600;font-size:0.75rem;cursor:pointer;white-space:nowrap;display:flex;align-items:center;gap:5px;transition:all 0.15s;backdrop-filter:blur(16px);box-shadow:0 3px 12px rgba(0,0,0,0.12)}
        .pill-btn:active{transform:scale(0.95)}.pill-primary{background:#10b981;color:white}.pill-secondary{background:rgba(255,255,255,0.92);color:#3b82f6;border:1px solid rgba(255,255,255,0.5)}.pill-neutral{background:rgba(255,255,255,0.92);color:#475569;border:1px solid rgba(255,255,255,0.5)}

        .legend-bar{position:fixed;bottom:68px;bottom:calc(68px + var(--sab));left:12px;right:12px;z-index:44;pointer-events:none;display:flex;justify-content:center}
        .legend-scroll{pointer-events:auto;display:flex;gap:5px;overflow-x:auto;padding:5px 10px;background:rgba(255,255,255,0.9);backdrop-filter:blur(16px);border-radius:16px;box-shadow:0 2px 10px rgba(0,0,0,0.08);max-width:100%;-webkit-overflow-scrolling:touch}
        .legend-item{display:flex;align-items:center;gap:4px;font-size:0.6rem;font-weight:600;color:#475569;white-space:nowrap;padding:3px 7px;border-radius:10px;background:#f8fafc;flex-shrink:0}
        .legend-dot{width:6px;height:6px;border-radius:50%;flex-shrink:0}

        .gps-btn{position:fixed;bottom:120px;bottom:calc(120px + var(--sab));right:14px;width:44px;height:44px;border-radius:50%;background:white;box-shadow:0 4px 16px rgba(0,0,0,0.18);cursor:pointer;z-index:44;display:flex;align-items:center;justify-content:center;font-size:1rem;color:#64748b;border:2px solid #e2e8f0;transition:all 0.3s}
        .gps-btn:active{transform:scale(0.9)}.gps-btn.active{background:#10b981;color:white;border-color:#10b981;animation:gpsPulse 2s infinite}
        @keyframes gpsPulse{0%,100%{box-shadow:0 4px 16px rgba(16,185,129,0.4)}50%{box-shadow:0 4px 24px rgba(16,185,129,0.7)}}

        .nav-eta-bar{position:fixed;top:calc(100px + var(--sat));left:50%;transform:translateX(-50%);z-index:46;width:calc(100% - 24px);max-width:420px;background:rgba(255,255,255,0.95);backdrop-filter:blur(16px);border-radius:18px;padding:10px 14px;box-shadow:0 4px 20px rgba(0,0,0,0.12);display:none;align-items:center;gap:10px;border-left:3px solid #10b981;transition:all 0.3s ease}
        .nav-eta-bar.show{display:flex}.nav-eta-bar.hide{display:none!important;opacity:0;transform:translateX(-50%) translateY(-10px)}
        .nav-eta-icon{width:32px;height:32px;border-radius:50%;background:#f0fdf4;display:flex;align-items:center;justify-content:center;color:#10b981;font-size:0.9rem;flex-shrink:0}
        .nav-eta-info{flex:1;min-width:0}.nav-eta-dest{font-weight:700;font-size:0.8rem;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.nav-eta-time{font-size:0.7rem;color:#64748b}
        .nav-eta-toggle{width:30px;height:30px;border-radius:50%;background:#f0fdf4;border:none;color:#10b981;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:0.8rem;flex-shrink:0;transition:all 0.2s ease}
        .nav-eta-toggle:active{background:#dcfce7;transform:scale(0.9)}
        .nav-eta-close{width:30px;height:30px;border-radius:50%;background:#fee2e2;border:none;color:#ef4444;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:0.8rem;flex-shrink:0;transition:all 0.2s ease}
        .nav-eta-close:active{background:#fecaca;transform:scale(0.9)}

        .direction-popup{position:fixed;bottom:0;left:0;right:0;background:white;border-radius:18px 18px 0 0;box-shadow:0 -4px 24px rgba(0,0,0,0.15);z-index:200;transform:translateY(100%);transition:transform 0.35s cubic-bezier(0.32,0.72,0,1);max-height:40vh;display:flex;flex-direction:column}
        .direction-popup.show{transform:translateY(0)}.direction-handle{padding:10px 0;text-align:center;flex-shrink:0}.direction-handle-bar{width:32px;height:4px;background:#e2e8f0;border-radius:2px;margin:0 auto}
        .direction-content{padding:0 16px 16px;overflow-y:auto;flex:1;padding-bottom:calc(16px + var(--sab))}
        .direction-step{padding:8px 0;display:flex;align-items:flex-start;gap:8px;border-bottom:1px solid #f1f5f9;font-size:0.8rem;color:#475569}.direction-step:last-child{border-bottom:none}.direction-step-icon{width:18px;text-align:center;flex-shrink:0;font-size:0.75rem}

        .info-panel{position:fixed;bottom:0;left:0;right:0;background:white;border-radius:18px 18px 0 0;box-shadow:0 -4px 24px rgba(0,0,0,0.15);z-index:200;transform:translateY(100%);transition:transform 0.35s cubic-bezier(0.32,0.72,0,1);max-height:42vh;overflow-y:auto;padding:16px;padding-bottom:calc(16px + var(--sab))}
        .info-panel.show{transform:translateY(0)}.panel-handle{width:32px;height:4px;background:#e2e8f0;border-radius:2px;margin:0 auto 12px}
        .panel-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px}.panel-title{font-weight:700;font-size:0.95rem;color:#0f172a}
        .panel-close{background:#f1f5f9;border:none;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:0.85rem;color:#64748b;display:flex;align-items:center;justify-content:center}.panel-close:active{background:#fee2e2;color:#ef4444}
        .info-row{display:flex;align-items:center;gap:8px;padding:8px 10px;background:#f8fafc;border-radius:8px;margin-bottom:6px;font-size:0.8rem;color:#475569}
        .info-badge{display:inline-block;background:#dbeafe;color:#1d4ed8;padding:3px 10px;border-radius:10px;font-size:0.65rem;font-weight:700}
        .nav-btn{width:100%;padding:12px;background:#3b82f6;color:white;border:none;border-radius:12px;font-weight:700;font-size:0.85rem;cursor:pointer;margin-top:10px;transition:all 0.15s}.nav-btn:active{background:#1d4ed8;transform:scale(0.98)}

        .user-marker{width:16px;height:16px;background:#10b981;border-radius:50%;border:3px solid white;box-shadow:0 0 0 3px rgba(16,185,129,0.3);animation:userPulse 2s infinite}
        @keyframes userPulse{0%,100%{box-shadow:0 0 0 3px rgba(16,185,129,0.3)}50%{box-shadow:0 0 0 10px rgba(16,185,129,0.06)}}
        .dest-marker{width:20px;height:20px;background:#ef4444;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.25)}
        
        /* Real-time walking marker */
        .walking-live-marker{width:24px;height:24px;background:#f59e0b;border-radius:50%;border:3px solid white;box-shadow:0 0 0 6px rgba(245,158,11,0.3), 0 4px 12px rgba(0,0,0,0.3);animation:walkBounce 0.6s ease-in-out infinite, walkPulse 1.5s ease-in-out infinite}
        @keyframes walkBounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
        @keyframes walkPulse{0%,100%{box-shadow:0 0 0 6px rgba(245,158,11,0.3), 0 4px 12px rgba(0,0,0,0.3)}50%{box-shadow:0 0 0 12px rgba(245,158,11,0.1), 0 4px 12px rgba(0,0,0,0.3)}}

        .toast{position:fixed;bottom:140px;left:16px;right:16px;padding:12px 14px;background:white;border-radius:12px;box-shadow:0 6px 24px rgba(0,0,0,0.18);font-size:0.8rem;font-weight:500;z-index:250;display:flex;align-items:center;gap:8px}
        .toast-success{border-left:3px solid #10b981;color:#166534}.toast-error{border-left:3px solid #ef4444;color:#991b1b}.toast-info{border-left:3px solid #06b6d4;color:#0c4a6e}
        .hidden{display:none!important}
        .loading-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:300}
        .loading-card{background:white;padding:28px;border-radius:18px;text-align:center}
        .spinner{width:40px;height:40px;border:3px solid #e2e8f0;border-top-color:#3b82f6;border-radius:50%;animation:spin 0.7s linear infinite;margin:0 auto 14px}@keyframes spin{to{transform:rotate(360deg)}}

        @media(min-width:768px){.search-bar{left:20px;transform:none;max-width:360px}.nav-eta-bar{left:20px;transform:none;max-width:360px}.bottom-controls{left:20px;transform:none}.legend-bar{left:20px;right:auto;justify-content:flex-start}.info-panel{left:20px;right:auto;width:360px;border-radius:18px;bottom:20px;transform:translateY(calc(100% + 20px))}.info-panel.show{transform:translateY(0)}.direction-popup{left:20px;right:auto;width:360px;border-radius:18px;bottom:20px;transform:translateY(calc(100% + 20px))}.direction-popup.show{transform:translateY(0)}}
    </style>
</head>
<body>
    <div id="map"></div>

    <div class="header">
        <div class="header-brand"><span class="header-logo">🗺️</span><span class="header-title">CPSU Navigator</span></div>
        <div class="header-spacer"></div>
        <div class="live-badge" id="liveBadge"><div class="live-dot"></div>LIVE</div>
        <div class="header-btns">
            <a href="{{ url('/directory') }}" class="icon-btn"><i class="fas fa-list"></i></a>
            <a href="{{ url('/login') }}" class="icon-btn"><i class="fas fa-user-shield"></i></a>
        </div>
    </div>

    <div class="search-bar">
        <div class="search-wrapper"><i class="fas fa-search search-icon"></i><input type="text" id="searchInput" placeholder="Search offices..." autocomplete="off"><button class="search-clear" id="searchClear" onclick="clearSearch()"><i class="fas fa-times-circle"></i></button></div>
        <div class="search-suggestions" id="searchSuggestions"></div>
    </div>

    <div class="nav-eta-bar" id="navEtaBar">
        <div class="nav-eta-icon"><i class="fas fa-walking"></i></div>
        <div class="nav-eta-info"><div class="nav-eta-dest" id="navEtaDest">-</div><div class="nav-eta-time" id="navEtaTime">Calculating...</div></div>
        <button class="nav-eta-toggle" onclick="toggleDirectionPopup()" title="Directions"><i class="fas fa-chevron-up"></i></button>
        <button class="nav-eta-close" onclick="stopNavigation()"><i class="fas fa-times"></i></button>
    </div>

    <div class="direction-popup" id="directionPopup">
        <div class="direction-handle" onclick="toggleDirectionPopup()"><div class="direction-handle-bar"></div></div>
        <div class="direction-content" id="directionContent"></div>
    </div>

    <div class="bottom-controls">
        <button onclick="centerToUser()" class="pill-btn pill-primary"><i class="fas fa-location-dot"></i> My Location</button>
        <button onclick="findNearestOffice()" class="pill-btn pill-secondary"><i class="fas fa-building"></i> Nearest</button>
        <button onclick="resetMap()" class="pill-btn pill-neutral"><i class="fas fa-home"></i> Reset</button>
    </div>

    <div class="legend-bar"><div class="legend-scroll">
        <div class="legend-item"><div class="legend-dot" style="background:#ef4444;"></div>Admin</div>
        <div class="legend-item"><div class="legend-dot" style="background:#3b82f6;"></div>Academic</div>
        <div class="legend-item"><div class="legend-dot" style="background:#10b981;"></div>Facility</div>
        <div class="legend-item"><i class="fas fa-road" style="color:#3b82f6;font-size:0.55rem;"></i>Footwalk</div>
        <div class="legend-item"><i class="fas fa-route" style="color:#10b981;font-size:0.55rem;"></i>Route</div>
        <div class="legend-item"><i class="fas fa-arrow-right" style="color:#ef4444;font-size:0.55rem;"></i>Walk</div>
        <div class="legend-item"><div class="legend-dot" style="background:#f59e0b;"></div>Link</div>
    </div></div>

    <div class="gps-btn" id="gpsBtn" onclick="toggleLiveTracking()"><i class="fas fa-location-arrow"></i></div>

    <div class="info-panel" id="infoPanel">
        <div class="panel-handle"></div>
        <div class="panel-header"><h3 class="panel-title" id="officeName"></h3><button class="panel-close" onclick="closePanel()"><i class="fas fa-times"></i></button></div>
        <div id="officeDetails"></div>
        <button class="nav-btn" onclick="navigateToOffice()"><i class="fas fa-directions mr-2"></i> Navigate Here</button>
    </div>

    <div class="loading-overlay hidden" id="loadingOverlay"><div class="loading-card"><div class="spinner"></div><p style="color:#475569;font-weight:600;">Loading map...</p></div></div>

<script>
let map,markers={},officesData=[],footwalkPaths=[],pathGraph={};
let currentRoute=null,userMarker=null,userAccuracyCircle=null,destMarker=null;
let selectedOffice=null,connectionPoints=[],graphBuilt=false;
let isTracking=false,watchId=null,currentRoutePoints=[];
let lastUserPosition=null,walkToPathLine=null,walkFromPathLine=null;
let directionSteps=[],isNavigating=false;
let realtimeWalkingWatchId=null;

function initMap(){
    showLoading(true);
    map=L.map('map',{zoomControl:false,attributionControl:false,scrollWheelZoom:true,doubleClickZoom:true,touchZoom:true,dragging:true}).setView([9.853,122.890],18);
    L.control.zoom({position:'bottomright'}).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,detectRetina:true}).addTo(map);
    loadOffices();loadFootwalks();setupSearch();
    window.addEventListener('resize',()=>{clearTimeout(window._rt);window._rt=setTimeout(()=>map.invalidateSize(),150);});
    window.addEventListener('orientationchange',()=>setTimeout(()=>map.invalidateSize(),300));
    map.on('click',()=>{if(document.getElementById('infoPanel').classList.contains('show'))closePanel();if(document.getElementById('directionPopup').classList.contains('show'))toggleDirectionPopup();});
}

function setupSearch(){
    const i=document.getElementById('searchInput'),c=document.getElementById('searchClear'),s=document.getElementById('searchSuggestions');
    i.addEventListener('input',()=>{const q=i.value.trim();c.classList.toggle('visible',q.length>0);if(q.length>=2){const r=officesData.filter(o=>o.name.toLowerCase().includes(q.toLowerCase())||(o.building&&o.building.toLowerCase().includes(q.toLowerCase())));showSuggestions(r);}else s.classList.remove('show');});
    i.addEventListener('focus',()=>{const q=i.value.trim();if(q.length>=2){const r=officesData.filter(o=>o.name.toLowerCase().includes(q.toLowerCase())||(o.building&&o.building.toLowerCase().includes(q.toLowerCase())));showSuggestions(r);}});
    i.addEventListener('keydown',e=>{if(e.key==='Escape'){clearSearch();i.blur();}});
    document.addEventListener('click',e=>{if(!e.target.closest('.search-bar'))s.classList.remove('show');});
}
function showSuggestions(r){const c=document.getElementById('searchSuggestions');c.innerHTML=r.length?r.slice(0,6).map(o=>`<div class="suggestion-item" onclick="selectOffice('${o.office_id}')"><div class="suggestion-dot" style="background:${o.category==='Administrative'?'#ef4444':o.category==='Academic'?'#3b82f6':'#10b981'};"></div><div class="suggestion-info"><div class="suggestion-name">${o.name}</div><div class="suggestion-detail">${o.building||'Main'} · Rm ${o.room_number||'N/A'}</div></div></div>`).join(''):'<div style="padding:14px;text-align:center;color:#94a3b8;">No results</div>';c.classList.add('show');}
function clearSearch(){const i=document.getElementById('searchInput');i.value='';document.getElementById('searchClear').classList.remove('visible');document.getElementById('searchSuggestions').classList.remove('show');i.focus();}

async function loadOffices(){try{const r=await fetch('{{ route("offices.index") }}');const d=await r.json();if(d.success&&d.data&&d.data.length){officesData=d.data;d.data.forEach(o=>addMarker(o));}else loadDemoOffices();}catch(e){loadDemoOffices();}}
function loadDemoOffices(){officesData=[{office_id:1,name:'Administration',building:'Admin Bldg',room_number:'101',lat:9.8531,lng:122.8901,category:'Administrative'},{office_id:2,name:'Engineering',building:'Engg Block',room_number:'201',lat:9.8532,lng:122.8902,category:'Academic'},{office_id:3,name:'Library',building:'Learning Center',room_number:'GF',lat:9.8533,lng:122.8903,category:'Academic'},{office_id:4,name:'Student Center',building:'Student Hub',room_number:'Main',lat:9.8534,lng:122.8904,category:'Facilities'},{office_id:5,name:'Health Services',building:'Medical Bldg',room_number:'102',lat:9.8535,lng:122.8905,category:'Services'}];officesData.forEach(o=>addMarker(o));}
function addMarker(o){if(!o.lat||!o.lng)return;const c=o.category==='Administrative'?'#ef4444':o.category==='Academic'?'#3b82f6':'#10b981';const icon=L.divIcon({html:`<div style="background:${c};width:14px;height:14px;border-radius:50%;border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.2);"></div>`,iconSize:[14,14],className:''});const m=L.marker([o.lat,o.lng],{icon}).addTo(map);m.on('click',()=>selectOffice(o.office_id));m.bindTooltip(o.name,{direction:'top',offset:[0,-8]});markers[o.office_id]={marker:m,data:o};}

async function loadFootwalks(){try{const r=await fetch('{{ route("footwalks.index") }}');const d=await r.json();if(d.success&&d.data?.length>0){footwalkPaths=d.data.map(f=>({id:f.id,name:f.name,color:f.color||'#3b82f6',coordinates:JSON.parse(f.coordinates)}));processFootwalks();}else createDemoFootwalks();}catch(e){createDemoFootwalks();}finally{showLoading(false);}}
function processFootwalks(){footwalkPaths.forEach(p=>drawFootwalk(p));buildPathGraph();showConnectionPoints();graphBuilt=true;showToast(`${footwalkPaths.length} paths loaded`,'success');}
function createDemoFootwalks(){const p=[];for(let i=0;i<officesData.length-1;i++)p.push({id:i+1,name:`Path ${i+1}`,color:'#3b82f6',coordinates:[[officesData[i].lat,officesData[i].lng],[officesData[i+1].lat,officesData[i+1].lng]]});if(officesData.length>=3)p.push({id:p.length+1,name:'Cross A',color:'#3b82f6',coordinates:[[officesData[0].lat,officesData[0].lng],[officesData[2].lat,officesData[2].lng]]});if(officesData.length>=5){p.push({id:p.length+1,name:'Cross B',color:'#3b82f6',coordinates:[[officesData[1].lat,officesData[1].lng],[officesData[3].lat,officesData[3].lng]]});p.push({id:p.length+1,name:'Cross C',color:'#3b82f6',coordinates:[[officesData[2].lat,officesData[2].lng],[officesData[4].lat,officesData[4].lng]]});}footwalkPaths=p;p.forEach(p=>drawFootwalk(p));buildPathGraph();graphBuilt=true;}
function drawFootwalk(p){try{L.polyline(p.coordinates.map(c=>[c[0],c[1]]),{color:p.color||'#3b82f6',weight:2.5,opacity:0.45,dashArray:'5,4'}).addTo(map);}catch(e){}}

function buildPathGraph(){pathGraph={};let nid=0;const p2i=new Map();footwalkPaths.forEach(p=>{p.coordinates.forEach(c=>{const k=`${c[0].toFixed(7)},${c[1].toFixed(7)}`;if(!p2i.has(k)){p2i.set(k,`n${nid++}`);pathGraph[p2i.get(k)]={lat:c[0],lng:c[1],edges:[],pathName:p.name};}});});footwalkPaths.forEach(p=>{for(let i=0;i<p.coordinates.length-1;i++){const k1=`${p.coordinates[i][0].toFixed(7)},${p.coordinates[i][1].toFixed(7)}`,k2=`${p.coordinates[i+1][0].toFixed(7)},${p.coordinates[i+1][1].toFixed(7)}`,id1=p2i.get(k1),id2=p2i.get(k2);if(id1&&id2){const d=calcDist(p.coordinates[i][0],p.coordinates[i][1],p.coordinates[i+1][0],p.coordinates[i+1][1]);if(!pathGraph[id1].edges.some(e=>e.to===id2)){pathGraph[id1].edges.push({to:id2,dist:d});pathGraph[id2].edges.push({to:id1,dist:d});}}}});connectionPoints=[];const CD=20;for(let i=0;i<footwalkPaths.length;i++){for(let j=i+1;j<footwalkPaths.length;j++){const cl=findClosest(footwalkPaths[i].coordinates,footwalkPaths[j].coordinates);if(cl.distance<CD){const k1=`${cl.p1.lat.toFixed(7)},${cl.p1.lng.toFixed(7)}`,k2=`${cl.p2.lat.toFixed(7)},${cl.p2.lng.toFixed(7)}`;let id1=p2i.get(k1),id2=p2i.get(k2);if(!id1){id1=`n${nid++}`;p2i.set(k1,id1);pathGraph[id1]={lat:cl.p1.lat,lng:cl.p1.lng,edges:[],isConnection:true};}if(!id2){id2=`n${nid++}`;p2i.set(k2,id2);pathGraph[id2]={lat:cl.p2.lat,lng:cl.p2.lng,edges:[],isConnection:true};}const cd=calcDist(cl.p1.lat,cl.p1.lng,cl.p2.lat,cl.p2.lng);if(!pathGraph[id1].edges.some(e=>e.to===id2)){pathGraph[id1].edges.push({to:id2,dist:cd});pathGraph[id2].edges.push({to:id1,dist:cd});}connectionPoints.push({lat:(cl.p1.lat+cl.p2.lat)/2,lng:(cl.p1.lng+cl.p2.lng)/2});}}}}
function findClosest(c1,c2){let md=Infinity,bp1={lat:c1[0][0],lng:c1[0][1]},bp2={lat:c2[0][0],lng:c2[0][1]};for(let i=0;i<c1.length-1;i++)for(let j=0;j<c2.length-1;j++)for(let t1=0;t1<=20;t1++)for(let t2=0;t2<=20;t2++){const la1=c1[i][0]+(t1/20)*(c1[i+1][0]-c1[i][0]),ln1=c1[i][1]+(t1/20)*(c1[i+1][1]-c1[i][1]),la2=c2[j][0]+(t2/20)*(c2[j+1][0]-c2[j][0]),ln2=c2[j][1]+(t2/20)*(c2[j+1][1]-c2[j][1]),d=calcDist(la1,ln1,la2,ln2);if(d<md){md=d;bp1={lat:la1,lng:ln1};bp2={lat:la2,lng:ln2};}}return{p1:bp1,p2:bp2,distance:md};}
function showConnectionPoints(){connectionPoints.forEach(p=>{L.circleMarker([p.lat,p.lng],{radius:4,color:'#f59e0b',fillColor:'#f59e0b',fillOpacity:0.7,weight:2}).addTo(map);});}
function calcDist(lat1,lng1,lat2,lng2){const R=6371000,dLat=(lat2-lat1)*Math.PI/180,dLng=(lng2-lng1)*Math.PI/180,a=Math.sin(dLat/2)**2+Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLng/2)**2;return R*2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a));}
function findNearestNode(lat,lng){let n=null,md=Infinity;Object.keys(pathGraph).forEach(id=>{const d=calcDist(lat,lng,pathGraph[id].lat,pathGraph[id].lng);if(d<md){md=d;n={id,node:pathGraph[id],dist:d};}});return n;}

function findShortestPath(sl,sn,el,en){
    if(!Object.keys(pathGraph).length)return[[sl,sn],[el,en]];
    const snode=findNearestNode(sl,sn),enode=findNearestNode(el,en);
    if(!snode||!enode)return[[sl,sn],[el,en]];
    const dists={},prev={},unv=new Set();Object.keys(pathGraph).forEach(id=>{dists[id]=Infinity;unv.add(id);});dists[snode.id]=0;
    while(unv.size){let cur=null,md=Infinity;unv.forEach(id=>{if(dists[id]<md){md=dists[id];cur=id;}});if(!cur||cur===enode.id)break;unv.delete(cur);pathGraph[cur]?.edges.forEach(e=>{if(unv.has(e.to)){const a=dists[cur]+e.dist;if(a<dists[e.to]){dists[e.to]=a;prev[e.to]=cur;}}});}
    if(dists[enode.id]===Infinity)return[[sl,sn],[el,en]];
    const pids=[];let c=enode.id,sf=0;while(c&&c!==snode.id&&sf++<1000){pids.unshift(c);c=prev[c];}pids.unshift(snode.id);
    const pts=[[sl,sn]];if(snode.dist>0.5)pts.push([pathGraph[snode.id].lat,pathGraph[snode.id].lng]);pids.forEach(id=>{if(pathGraph[id])pts.push([pathGraph[id].lat,pathGraph[id].lng]);});if(enode.dist>0.5)pts.push([pathGraph[enode.id].lat,pathGraph[enode.id].lng]);pts.push([el,en]);
    const uq=[];pts.forEach(p=>{if(!uq.length||Math.abs(uq[uq.length-1][0]-p[0])>1e-6||Math.abs(uq[uq.length-1][1]-p[1])>1e-6)uq.push(p);});return uq;
}

function selectOffice(oid){
    const o=officesData.find(o=>o.office_id==oid);if(!o)return;
    if(isNavigating&&selectedOffice&&selectedOffice.office_id!==o.office_id)stopNavigation();
    selectedOffice=o;
    document.getElementById('officeName').textContent=o.name;
    document.getElementById('officeDetails').innerHTML=`<div class="info-row"><i class="fas fa-building" style="color:#3b82f6;"></i><span>${o.building||'Main'}</span></div><div class="info-row"><i class="fas fa-door-open" style="color:#3b82f6;"></i><span>Room ${o.room_number||'N/A'}</span></div><span class="info-badge">${o.category}</span>`;
    document.getElementById('infoPanel').classList.add('show');document.getElementById('searchSuggestions').classList.remove('show');
    document.getElementById('searchInput').value=o.name;document.getElementById('searchClear').classList.add('visible');
    map.flyTo([o.lat,o.lng],18,{duration:0.7});if(markers[o.office_id])markers[o.office_id].marker.openTooltip();
}
function closePanel(){document.getElementById('infoPanel').classList.remove('show');}
function toggleDirectionPopup(){const p=document.getElementById('directionPopup'),b=document.querySelector('.nav-eta-toggle i');p.classList.toggle('show');if(b)b.className=p.classList.contains('show')?'fas fa-chevron-down':'fas fa-chevron-up';}

function navigateToOffice(){
    if(!selectedOffice){showToast('Select an office first','error');return;}
    if(!graphBuilt){showToast('Paths loading...','error');return;}
    if(isNavigating)stopNavigation();
    showToast('Getting location...','info');
    navigator.geolocation.getCurrentPosition(pos=>{
        lastUserPosition={lat:pos.coords.latitude,lng:pos.coords.longitude};
        calculateAndDrawRoute(pos.coords.latitude,pos.coords.longitude,selectedOffice.lat,selectedOffice.lng);
        isNavigating=true;
        startRealtimeWalking();
    },()=>showToast('Enable location services','error'),{enableHighAccuracy:true,timeout:10000});
}

function calculateAndDrawRoute(sl,sn,el,en,recalc=false){
    const pts=findShortestPath(sl,sn,el,en);currentRoutePoints=pts;clearRoutes();
    walkToPathLine=L.polyline([pts[0],pts[1]],{color:'#ef4444',weight:3.5,dashArray:'7,5',opacity:0.8}).addTo(map);
    currentRoute=L.polyline(pts.slice(1,pts.length-1),{color:'#10b981',weight:5,dashArray:'10,7',opacity:0.9,lineCap:'round',lineJoin:'round'}).addTo(map);
    walkFromPathLine=L.polyline([pts[pts.length-2],pts[pts.length-1]],{color:'#ef4444',weight:3.5,dashArray:'7,5',opacity:0.8}).addTo(map);
    const dIcon=L.divIcon({html:'<div class="dest-marker"></div>',iconSize:[20,20],iconAnchor:[10,20]});destMarker=L.marker([el,en],{icon:dIcon}).addTo(map);
    
    // Replace user marker with animated walking marker
    if(userMarker){map.removeLayer(userMarker);userMarker=null;}
    updateWalkingMarker(sl,sn);
    
    const wtd=calcDist(sl,sn,pts[1][0],pts[1][1]),wfd=calcDist(pts[pts.length-2][0],pts[pts.length-2][1],el,en);let fwd=0;for(let i=1;i<pts.length-2;i++)fwd+=calcDist(pts[i][0],pts[i][1],pts[i+1][0],pts[i+1][1]);const td=wtd+fwd+wfd;
    const eta=document.getElementById('navEtaBar');eta.classList.remove('hide');eta.classList.add('show');
    document.getElementById('navEtaDest').textContent=selectedOffice.name;document.getElementById('navEtaTime').textContent=`${Math.round(td)}m · ${(td/80).toFixed(1)} min`;
    directionSteps=[{icon:'fa-location-dot',color:'#10b981',text:'You are here'},{icon:'fa-arrow-right',color:'#ef4444',text:`Walk <b>${Math.round(wtd)}m</b> to nearest footwalk <span style="color:#ef4444;">(red)</span>`},{icon:'fa-road',color:'#10b981',text:`Follow footwalks <b>${Math.round(fwd)}m</b> <span style="color:#10b981;">(green)</span>`},{icon:'fa-arrow-right',color:'#ef4444',text:`Walk <b>${Math.round(wfd)}m</b> to office <span style="color:#ef4444;">(red)</span>`},{icon:'fa-flag-checkered',color:'#3b82f6',text:`Arrive at <b>${selectedOffice.name}</b>`}];
    updateDirectionContent();document.getElementById('directionPopup').classList.remove('show');const ti=document.querySelector('.nav-eta-toggle i');if(ti)ti.className='fas fa-chevron-up';closePanel();
    if(!recalc){map.fitBounds(L.latLngBounds([pts[0],pts[1],...pts.slice(1,pts.length-1),pts[pts.length-2],pts[pts.length-1]]),{padding:[80,80],maxZoom:18});showToast(`Route: ${Math.round(td)}m`,'success');}
}

function updateWalkingMarker(lat,lng){
    if(userMarker){map.removeLayer(userMarker);userMarker=null;}
    if(userAccuracyCircle){map.removeLayer(userAccuracyCircle);userAccuracyCircle=null;}
    userMarker=L.marker([lat,lng],{
        icon:L.divIcon({
            html:'<div class="walking-live-marker"></div>',
            iconSize:[24,24],
            iconAnchor:[12,12],
            className:''
        }),
        zIndexOffset:1000
    }).addTo(map);
}

function startRealtimeWalking(){
    stopRealtimeWalking();
    document.getElementById('liveBadge').classList.add('show');
    document.getElementById('gpsBtn').classList.add('active');
    showToast('Real-time navigation active - start walking!','success');
    
    realtimeWalkingWatchId = navigator.geolocation.watchPosition(
        pos => {
            const {latitude, longitude, accuracy} = pos.coords;
            lastUserPosition = {lat: latitude, lng: longitude};
            
            // Update walking marker position with real GPS coordinates
            updateWalkingMarker(latitude, longitude);
            
            // Update ETA based on real position
            if (selectedOffice) {
                const distance = calcDist(latitude, longitude, selectedOffice.lat, selectedOffice.lng);
                document.getElementById('navEtaTime').textContent = `${Math.round(distance)}m · ${(distance/80).toFixed(1)} min`;
                
                // Check if arrived
                if (distance < 15) {
                    showToast(`🎉 You've arrived at ${selectedOffice.name}!`,'success');
                    if (navigator.vibrate) navigator.vibrate([200, 100, 200, 100, 200]);
                    stopNavigation();
                }
            }
            
            // Keep map centered on user while walking
            map.setView([latitude, longitude], 18, {animate: true, duration: 0.5});
        },
        err => {
            if (err.code === 1) {
                showToast('GPS permission denied','error');
                stopNavigation();
            }
        },
        {
            enableHighAccuracy: true,
            maximumAge: 1000,
            timeout: 10000
        }
    );
}

function stopRealtimeWalking(){
    if(realtimeWalkingWatchId){
        navigator.geolocation.clearWatch(realtimeWalkingWatchId);
        realtimeWalkingWatchId = null;
    }
}

function clearRoutes(){
    stopRealtimeWalking();
    if(currentRoute){map.removeLayer(currentRoute);currentRoute=null;}
    if(walkToPathLine){map.removeLayer(walkToPathLine);walkToPathLine=null;}
    if(walkFromPathLine){map.removeLayer(walkFromPathLine);walkFromPathLine=null;}
    if(destMarker){map.removeLayer(destMarker);destMarker=null;}
}
function updateDirectionContent(){document.getElementById('directionContent').innerHTML=directionSteps.map(s=>`<div class="direction-step"><div class="direction-step-icon"><i class="fas ${s.icon}" style="color:${s.color};"></i></div><div>${s.text}</div></div>`).join('');}

function startLiveTracking(){if(watchId)return;isTracking=true;document.getElementById('liveBadge').classList.add('show');document.getElementById('gpsBtn').classList.add('active');showToast('Live GPS active','success');watchId=navigator.geolocation.watchPosition(pos=>{const{latitude,longitude,accuracy}=pos.coords;lastUserPosition={lat:latitude,lng:longitude};if(!isNavigating){updateUserMarker(latitude,longitude,accuracy);}if(isTracking)map.setView([latitude,longitude],18,{animate:true,duration:0.5});},err=>{if(err.code===1){showToast('GPS permission denied','error');stopLiveTracking();}},{enableHighAccuracy:true,maximumAge:2000,timeout:15000});}
function stopLiveTracking(){if(watchId){navigator.geolocation.clearWatch(watchId);watchId=null;}isTracking=false;document.getElementById('liveBadge').classList.remove('show');document.getElementById('gpsBtn').classList.remove('active');}
function toggleLiveTracking(){isTracking?(stopLiveTracking(),showToast('Tracking off','info')):startLiveTracking();}
function centerToUser(){navigator.geolocation.getCurrentPosition(pos=>{updateUserMarker(pos.coords.latitude,pos.coords.longitude);map.setView([pos.coords.latitude,pos.coords.longitude],18);if(!isTracking)startLiveTracking();showToast('Location found','success');},()=>showToast('Unable to get location','error'),{enableHighAccuracy:true,timeout:8000});}
function stopNavigation(){
    stopRealtimeWalking();
    clearRoutes();
    if(userMarker){map.removeLayer(userMarker);userMarker=null;}
    if(userAccuracyCircle){map.removeLayer(userAccuracyCircle);userAccuracyCircle=null;}
    const eta=document.getElementById('navEtaBar');eta.classList.add('hide');eta.classList.remove('show');
    document.getElementById('directionPopup').classList.remove('show');
    const ti=document.querySelector('.nav-eta-toggle i');if(ti)ti.className='fas fa-chevron-up';
    currentRoutePoints=[];directionSteps=[];isNavigating=false;
    stopLiveTracking();
}
function findNearestOffice(){navigator.geolocation.getCurrentPosition(pos=>{let n=null,md=Infinity;officesData.forEach(o=>{if(o.lat&&o.lng){const d=calcDist(pos.coords.latitude,pos.coords.longitude,o.lat,o.lng);if(d<md){md=d;n=o;}}});if(n){showToast(`Nearest: ${n.name} (${Math.round(md)}m)`,'success');selectOffice(n.office_id);}},()=>showToast('Enable location','error'),{enableHighAccuracy:true,timeout:8000});}
function resetMap(){
    stopNavigation();
    closePanel();
    stopLiveTracking();
    stopRealtimeWalking();
    if(userMarker){map.removeLayer(userMarker);userMarker=null;}
    map.setView([9.853,122.890],18);
    document.getElementById('searchInput').value='';
    document.getElementById('searchClear').classList.remove('visible');
    document.getElementById('searchSuggestions').classList.remove('show');
}
function updateUserMarker(lat,lng,acc=null){
    if(userMarker)map.removeLayer(userMarker);
    if(userAccuracyCircle)map.removeLayer(userAccuracyCircle);
    userMarker=L.marker([lat,lng],{icon:L.divIcon({html:'<div class="user-marker"></div>',iconSize:[16,16],className:''}),zIndexOffset:1000}).addTo(map);
    if(acc&&acc<50)userAccuracyCircle=L.circle([lat,lng],{radius:acc,color:'#10b981',weight:1,opacity:0.25,fillOpacity:0.06}).addTo(map);
}
function showToast(msg,type){const ex=document.querySelector('.toast');if(ex){ex.style.opacity='0';ex.style.transform='translateY(10px)';ex.style.transition='all 0.25s';setTimeout(()=>ex.remove(),250);}const t=document.createElement('div');t.className=`toast toast-${type}`;t.innerHTML=`<i class="fas fa-${type==='success'?'check-circle':type==='error'?'exclamation-circle':'info-circle'}"></i> ${msg}`;document.body.appendChild(t);setTimeout(()=>{t.style.opacity='0';t.style.transform='translateY(10px)';t.style.transition='all 0.3s';setTimeout(()=>t.remove(),300);},type==='error'?4000:2500);}
function showLoading(s){document.getElementById('loadingOverlay').classList.toggle('hidden',!s);}
document.addEventListener('DOMContentLoaded',initMap);
</script>
</body>
</html>