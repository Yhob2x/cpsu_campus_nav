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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        #map { height: 550px; border-radius: 12px; z-index: 1; }
        .modal { transition: all 0.3s ease; }
        .tab-active { border-bottom: 3px solid #3b82f6; color: #2563eb; }
        .sidebar { scrollbar-width: thin; }
        .sidebar::-webkit-scrollbar { width: 6px; }
        .drawing-mode-active { background-color: #22c55e !important; }
        .endpoint-marker {
            background: #8b5cf6; border-radius: 50%; width: 12px; height: 12px;
            border: 3px solid white; box-shadow: 0 0 8px rgba(139,92,246,0.5);
            cursor: pointer; animation: endpointPulse 2s infinite;
        }
        @keyframes endpointPulse {
            0%,100%{box-shadow:0 0 8px rgba(139,92,246,0.5)}50%{box-shadow:0 0 16px rgba(139,92,246,0.8)}
        }
        .endpoint-marker:hover{transform:scale(1.4);background:#7c3aed}
        .midpoint-marker {
            background: #06b6d4; border-radius: 50%; width: 10px; height: 10px;
            border: 2px solid white; box-shadow: 0 0 6px rgba(6,182,212,0.5);
            cursor: pointer; animation: midpointPulse 3s infinite;
        }
        @keyframes midpointPulse {
            0%,100%{box-shadow:0 0 6px rgba(6,182,212,0.5)}50%{box-shadow:0 0 12px rgba(6,182,212,0.8)}
        }
        .midpoint-marker:hover{transform:scale(1.5);background:#0891b2}
        .toast {
            position:fixed;bottom:20px;right:20px;z-index:10000;
            padding:12px 20px;border-radius:8px;color:white;font-size:14px;
            animation:slideIn 0.3s ease;
        }
        @keyframes slideIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}
        .toast-success{background:#10b981}.toast-error{background:#ef4444}.toast-info{background:#3b82f6}
        .list-item{transition:all 0.2s ease}.list-item:hover{transform:translateX(2px);box-shadow:0 2px 8px rgba(0,0,0,0.1)}
        .list-item.deleting{opacity:0;transform:translateX(20px)}
        .extend-btn{background:#8b5cf6;color:white;padding:2px 8px;border-radius:4px;font-size:11px;cursor:pointer}
        .extend-btn:hover{background:#7c3aed}
        .branch-btn{background:#06b6d4;color:white;padding:2px 8px;border-radius:4px;font-size:11px;cursor:pointer}
        .branch-btn:hover{background:#0891b2}
    </style>
</head>
<body class="bg-gray-100">
<div class="flex h-screen">
<div class="w-80 bg-white shadow-xl flex flex-col sidebar overflow-y-auto">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center"><span class="text-3xl mr-2">🗺️</span><div><h1 class="text-xl font-bold text-gray-800">CPSU Admin</h1><p class="text-xs text-gray-500">Pathway Manager</p></div></div>
    </div>
    <div class="flex border-b border-gray-200">
        <button onclick="switchTab('offices')" id="tabOfficesBtn" class="flex-1 py-3 text-center font-medium tab-active">🏢 Offices</button>
        <button onclick="switchTab('paths')" id="tabPathsBtn" class="flex-1 py-3 text-center font-medium text-gray-600">🛤️ Footwalks</button>
        <button onclick="switchTab('connections')" id="tabConnectionsBtn" class="flex-1 py-3 text-center font-medium text-gray-600">🔗 Links</button>
    </div>
    <div id="officesTab" class="p-4">
        <button onclick="prepareNewOffice()" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 mb-4 flex items-center justify-center"><i class="fas fa-plus mr-2"></i> Add Office (Click Map)</button>
        <input type="text" id="searchOffice" placeholder="Search offices..." class="w-full px-3 py-2 border rounded-lg text-sm mb-3 pl-8">
        <div id="officesList" class="space-y-2 max-h-[calc(100vh-320px)] overflow-y-auto"></div>
    </div>
    <div id="pathsTab" class="p-4 hidden">
        <div class="bg-blue-50 rounded-lg p-3 mb-4 text-xs"><b>Purple</b>=endpoints | <b style="color:#06b6d4">Cyan</b>=branch points | Click dots to extend/branch!</div>
        <button id="drawPathBtn" onclick="startNewDrawing()" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 mb-4 flex items-center justify-center"><i class="fas fa-draw-polygon mr-2"></i> Draw New Footwalk</button>
        <input type="text" id="searchPath" placeholder="Search footwalks..." class="w-full px-3 py-2 border rounded-lg text-sm mb-3 pl-8">
        <div id="pathsList" class="space-y-2 max-h-[calc(100vh-380px)] overflow-y-auto"></div>
        <div class="mt-4 bg-gray-50 rounded-lg p-3 text-xs">
            <div class="flex justify-between"><span>Connect Dist:</span><input type="number" id="connectionDistance" value="5" step="1" min="1" max="50" class="w-16 border rounded px-1">m</div>
            <div class="flex justify-between mt-2"><span>Connected:</span><span id="connectionCount">0</span></div>
            <button onclick="recalculateConnections()" class="w-full mt-2 bg-purple-100 text-purple-700 py-1 rounded hover:bg-purple-200"><i class="fas fa-sync-alt mr-1"></i>Recalculate</button>
        </div>
    </div>
    <div id="connectionsTab" class="p-4 hidden">
        <div id="connectionsList" class="space-y-2 max-h-[calc(100vh-300px)] overflow-y-auto"></div>
    </div>
    <div class="p-4 border-t border-gray-200 mt-auto">
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-full bg-red-50 text-red-600 py-2 rounded-lg hover:bg-red-100"><i class="fas fa-sign-out-alt mr-2"></i>Logout</button></form>
    </div>
</div>

<div class="flex-1 p-4">
    <div class="bg-white rounded-lg shadow-lg p-4 mb-4">
        <div class="flex justify-between flex-wrap gap-2">
            <div><h2 class="text-lg font-bold">Pathway Editor</h2><p class="text-xs text-gray-500"><b>Click map</b>=add office | Draw=footwalk | Purple=endpoints | Cyan=branch points</p></div>
            <div class="flex gap-2">
                <span id="drawStatus" class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full"><i class="fas fa-pencil-alt mr-1"></i> OFF</span>
                <button onclick="toggleAllMarkers()" class="bg-cyan-500 text-white px-3 py-1 rounded text-sm hover:bg-cyan-600"><i class="fas fa-dot-circle mr-1"></i><span id="markerBtnText">Hide Markers</span></button>
                <button onclick="showAllConnections()" class="bg-orange-500 text-white px-3 py-1 rounded text-sm hover:bg-orange-600"><i class="fas fa-link mr-1"></i>Links</button>
                <button onclick="resetMapView()" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600"><i class="fas fa-home mr-1"></i>Reset</button>
            </div>
        </div>
    </div>
    <div id="map" class="shadow-lg"></div>
</div>
</div>

<!-- Office Modal -->
<div id="officeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 modal">
    <div class="bg-white rounded-lg w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto p-6">
        <div class="flex justify-between mb-4"><h3 class="text-xl font-bold" id="officeModalTitle">Add Office</h3><button onclick="closeOfficeModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button></div>
        <form id="officeForm">
            <input type="hidden" id="officeId">
            <div class="space-y-3">
                <div><label class="block text-sm mb-1">Name *</label><input type="text" id="officeName" required class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-sm mb-1">Building *</label><input type="text" id="officeBuilding" required class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-sm mb-1">Room</label><input type="text" id="officeRoom" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-sm mb-1">Category</label><select id="officeCategory" class="w-full border rounded-lg px-3 py-2 text-sm"><option>Administrative</option><option>Academic</option><option>Facility</option></select></div>
                <div class="grid grid-cols-2 gap-3"><div><label class="block text-sm mb-1">Latitude</label><input type="text" id="officeLat" readonly class="w-full border rounded-lg px-3 py-2 text-sm bg-gray-50"></div><div><label class="block text-sm mb-1">Longitude</label><input type="text" id="officeLng" readonly class="w-full border rounded-lg px-3 py-2 text-sm bg-gray-50"></div></div>
                <p class="text-xs text-blue-600"><i class="fas fa-info-circle"></i> Click on the map to set the location before saving</p>
            </div>
            <div class="flex gap-2 mt-6"><button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">Save Office</button><button type="button" onclick="closeOfficeModal()" class="flex-1 bg-gray-300 py-2 rounded-lg hover:bg-gray-400">Cancel</button></div>
        </form>
    </div>
</div>

<!-- Path Modal -->
<div id="pathModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 modal">
    <div class="bg-white rounded-lg w-full max-w-md mx-4 p-6">
        <div class="flex justify-between mb-4"><h3 class="text-xl font-bold" id="pathModalTitle">Footwalk</h3><button onclick="closePathModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button></div>
        <form id="pathForm">
            <input type="hidden" id="pathId">
            <div class="space-y-3">
                <div><label class="block text-sm mb-1">Name *</label><input type="text" id="pathName" required class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-sm mb-1">Type</label><select id="pathType" class="w-full border rounded-lg px-3 py-2 text-sm"><option value="walkway">Walkway</option><option value="paved">Paved</option><option value="gravel">Gravel</option><option value="stairs">Stairs</option><option value="ramp">Ramp</option></select></div>
                <div><label class="block text-sm mb-1">Color</label><input type="color" id="pathColor" value="#3b82f6" class="w-full h-10 border rounded-lg"></div>
                <div><label class="block text-sm mb-1">Width (m)</label><input type="number" id="pathWidth" value="2" step="0.5" min="0.5" class="w-full border rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-sm mb-1">Description</label><textarea id="pathDescription" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea></div>
                <div class="text-xs text-gray-500 bg-gray-50 p-2 rounded">Length: <span id="pathLength">0</span>m</div>
            </div>
            <div class="flex gap-2 mt-6">
                <button type="submit" class="flex-1 bg-green-600 text-white py-2 rounded-lg hover:bg-green-700">Save Footwalk</button>
                <button type="button" onclick="deleteCurrentPath()" class="flex-1 bg-red-600 text-white py-2 rounded-lg hover:bg-red-700" id="deletePathBtn" style="display:none">Delete</button>
                <button type="button" onclick="closePathModal()" class="flex-1 bg-gray-300 py-2 rounded-lg hover:bg-gray-400">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
let map,markers={},drawnPaths={},connectionMarkers=[],endpointMarkers=[],midpointMarkers=[];
let currentDrawControl=null,isDrawing=false,currentPathCoords=[];
let footwalksData=[],officesData=[],selectedLatLng=null,showAllMarkersFlag=true;
const csrf=document.querySelector('meta[name="csrf-token"]').content;

// ============ INIT ============
function initMap(){
    map=L.map('map',{scrollWheelZoom:true,doubleClickZoom:true,touchZoom:true,dragging:true}).setView([9.853,122.890],17);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19}).addTo(map);
    
    // Map click - ONLY add office when NOT drawing
    map.on('click',function(e){
        if(isDrawing) return;
        selectedLatLng = e.latlng;
        showOfficeModal();
    });
    
    loadOffices();loadFootwalks();
}

// ============ OFFICE FUNCTIONS ============
function showOfficeModal(){
    document.getElementById('officeId').value='';
    document.getElementById('officeName').value='';
    document.getElementById('officeBuilding').value='';
    document.getElementById('officeRoom').value='';
    document.getElementById('officeCategory').value='Academic';
    document.getElementById('officeHours') && (document.getElementById('officeHours').value='');
    document.getElementById('officeContact') && (document.getElementById('officeContact').value='');
    
    // Set coordinates from map click
    if(selectedLatLng){
        document.getElementById('officeLat').value = selectedLatLng.lat.toFixed(8);
        document.getElementById('officeLng').value = selectedLatLng.lng.toFixed(8);
    } else {
        document.getElementById('officeLat').value = '';
        document.getElementById('officeLng').value = '';
    }
    
    document.getElementById('officeModalTitle').innerText = 'Add New Office';
    document.getElementById('officeModal').classList.remove('hidden');
    document.getElementById('officeModal').classList.add('flex');
}

function prepareNewOffice(){
    selectedLatLng = null;
    document.getElementById('officeLat').value = '';
    document.getElementById('officeLng').value = '';
    showOfficeModal();
}

document.getElementById('officeForm').addEventListener('submit',async(e)=>{
    e.preventDefault();
    const lat = document.getElementById('officeLat').value;
    const lng = document.getElementById('officeLng').value;
    
    if(!lat || !lng){
        showToast('Please click on the map to set the office location','error');
        return;
    }
    
    const data = {
        name: document.getElementById('officeName').value,
        building: document.getElementById('officeBuilding').value,
        room_number: document.getElementById('officeRoom').value,
        category: document.getElementById('officeCategory').value,
        lat: lat,
        lng: lng
    };
    
    if(!data.name || !data.building) {
        showToast('Name and building are required','error');
        return;
    }
    
    try{
        const officeId = document.getElementById('officeId').value;
        let url, method;
        
        if (officeId) {
            // Update existing office
            url = '{{ route("offices.update", "") }}/' + officeId;
            method = 'PUT';
            data.office_id = officeId;
        } else {
            // Create new office
            url = '{{ route("offices.index") }}';
            method = 'POST';
        }
        
        const resp = await fetch(url,{
            method,
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
            body:JSON.stringify(data)
        });
        const result = await resp.json();
        if(result.success){
            showToast('Office saved!','success');
            closeOfficeModal();
            await loadOffices();
        } else {
            showToast('Error: '+(result.message||'Unknown'),'error');
        }
    }catch(err){
        console.error(err);
        showToast('Error saving office','error');
    }
});

function closeOfficeModal(){
    document.getElementById('officeModal').classList.add('hidden');
    document.getElementById('officeModal').classList.remove('flex');
    document.getElementById('officeForm').reset();
    document.getElementById('officeId').value = '';
    selectedLatLng = null;
}

async function editOffice(id){
    const o = officesData.find(o=>o.office_id==id);
    if(o){
        document.getElementById('officeId').value = o.office_id;
        document.getElementById('officeName').value = o.name;
        document.getElementById('officeBuilding').value = o.building;
        document.getElementById('officeRoom').value = o.room_number || '';
        document.getElementById('officeCategory').value = o.category || 'Academic';
        document.getElementById('officeLat').value = o.lat;
        document.getElementById('officeLng').value = o.lng;
        document.getElementById('officeModalTitle').innerText = 'Edit Office';
        document.getElementById('officeModal').classList.remove('hidden');
        document.getElementById('officeModal').classList.add('flex');
    }
}

function deleteOfficeItem(id){if(confirm('Delete this office?'))deleteOffice(id);}

async function deleteOffice(id){
    try{
        const el = document.getElementById(`office-item-${id}`);
        if(el) el.classList.add('deleting');
        if(markers[id]){map.removeLayer(markers[id]);delete markers[id];}
        const r = await fetch('{{ route("offices.delete", "") }}/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrf
            }
        });
        const d = await r.json();
        if(d.success){
            officesData = officesData.filter(o=>o.office_id!=id);
            displayOfficesList(officesData);
            showToast('Office deleted','success');
        } else {await loadOffices();}
    }catch(e){await loadOffices();}
}

async function loadOffices(){
    try{
        const r = await fetch('{{ route("offices.index") }}');
        const d = await r.json();
        if(d.success && d.data){
            officesData = d.data;
            displayOfficesList(d.data);
            d.data.forEach(o=>addMarkerToMap(o));
        }
    }catch(e){console.error(e);}
}

function addMarkerToMap(o){
    if(!o.lat||!o.lng)return;
    const c = o.category==='Administrative'?'#ef4444':o.category==='Academic'?'#3b82f6':'#10b981';
    const icon = L.divIcon({html:`<div style="background:${c};width:16px;height:16px;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3);"></div>`,iconSize:[20,20]});
    const m = L.marker([o.lat,o.lng],{icon}).addTo(map);
    m.bindPopup(`<div class="p-2"><b>${o.name}</b><br><span class="text-xs">${o.building}</span><br><div class="flex gap-1 mt-2"><button onclick="editOffice('${o.office_id}')" class="bg-blue-500 text-white px-2 py-1 rounded text-xs">Edit</button><button onclick="deleteOfficeItem('${o.office_id}')" class="bg-red-500 text-white px-2 py-1 rounded text-xs">Del</button></div></div>`);
    markers[o.office_id] = m;
}

function displayOfficesList(offices){
    document.getElementById('officesList').innerHTML = offices.length ? offices.map(o=>
        `<div class="bg-gray-50 rounded-lg p-3 list-item" id="office-item-${o.office_id}">
            <div class="flex justify-between">
                <div><h4 class="font-semibold text-sm">${o.name}</h4><p class="text-xs text-gray-500">${o.building}</p></div>
                <div><button onclick="editOffice('${o.office_id}')" class="text-blue-600 mr-2"><i class="fas fa-edit"></i></button><button onclick="deleteOfficeItem('${o.office_id}')" class="text-red-600"><i class="fas fa-trash"></i></button></div>
            </div>
        </div>`
    ).join('') : '<div class="text-center text-gray-400 p-4">No offices yet. Click on the map to add one!</div>';
}

// ============ DRAWING FUNCTIONS ============
function resetDrawingState(){
    isDrawing = false;
    if(currentDrawControl){try{currentDrawControl.disable();map.removeControl(currentDrawControl);}catch(e){}currentDrawControl=null;}
    map.off('draw:created');
    document.getElementById('drawPathBtn').classList.remove('drawing-mode-active');
    document.getElementById('drawPathBtn').innerHTML='<i class="fas fa-draw-polygon mr-2"></i> Draw New Footwalk';
    document.getElementById('drawStatus').innerHTML='<i class="fas fa-pencil-alt mr-1"></i> OFF';
    document.getElementById('drawStatus').className='text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full';
    window._drawOpts=null;window._extendPathId=null;window._extendEndpoint=null;window._branchLat=null;window._branchLng=null;
}

function startNewDrawing(){
    resetDrawingState();
    isDrawing = true;
    document.getElementById('drawPathBtn').classList.add('drawing-mode-active');
    document.getElementById('drawPathBtn').innerHTML='<i class="fas fa-stop mr-2"></i> Stop Drawing';
    document.getElementById('drawStatus').innerHTML='<i class="fas fa-draw-polygon mr-1"></i> NEW PATH';
    document.getElementById('drawStatus').className='text-sm text-green-700 bg-green-100 px-3 py-1 rounded-full';
    
    currentDrawControl = new L.Draw.Polyline(map,{
        shapeOptions:{color:'#22c55e',weight:5,opacity:0.7},
        showLength:true,metric:true,repeatMode:false
    });
    currentDrawControl.enable();
    
    map.on('draw:created',function(e){
        const layer = e.layer;
        currentPathCoords = layer.getLatLngs().map(ll=>[ll.lat,ll.lng]);
        map.removeLayer(layer);
        
        let len=0;
        for(let i=0;i<currentPathCoords.length-1;i++) len+=calcDist(currentPathCoords[i][0],currentPathCoords[i][1],currentPathCoords[i+1][0],currentPathCoords[i+1][1]);
        
        document.getElementById('pathId').value='';
        document.getElementById('pathName').value='';
        document.getElementById('pathType').value='walkway';
        document.getElementById('pathColor').value='#3b82f6';
        document.getElementById('pathWidth').value='2';
        document.getElementById('pathDescription').value='';
        document.getElementById('pathLength').innerText=len.toFixed(2);
        document.getElementById('pathModalTitle').innerText='New Footwalk';
        document.getElementById('deletePathBtn').style.display='none';
        document.getElementById('pathModal').classList.remove('hidden');
        document.getElementById('pathModal').classList.add('flex');
        
        window.tempPathCoords = currentPathCoords;
        resetDrawingState();
        showToast('Path drawn! Fill details and save.','success');
    });
}

function startExtendDrawing(pathId,endpoint){
    resetDrawingState();
    const path = footwalksData.find(f=>f.id==pathId);
    if(!path) return;
    
    isDrawing = true;
    document.getElementById('drawPathBtn').classList.add('drawing-mode-active');
    document.getElementById('drawPathBtn').innerHTML='<i class="fas fa-stop mr-2"></i> Stop';
    document.getElementById('drawStatus').innerHTML='<i class="fas fa-arrow-right mr-1"></i> EXTEND: '+path.name;
    document.getElementById('drawStatus').className='text-sm text-purple-700 bg-purple-100 px-3 py-1 rounded-full';
    
    currentDrawControl = new L.Draw.Polyline(map,{
        shapeOptions:{color:'#8b5cf6',weight:5,opacity:0.7},
        showLength:true,metric:true,repeatMode:false
    });
    currentDrawControl.enable();
    window._extendPathId = pathId;
    window._extendEndpoint = endpoint;
    
    try{
        const c = JSON.parse(path.coordinates);
        const p = endpoint==='start' ? c[0] : c[c.length-1];
        map.setView([p[0],p[1]],19);
    }catch(e){}
    
    map.on('draw:created',function(e){
        const layer = e.layer;
        const newCoords = layer.getLatLngs().map(ll=>[ll.lat,ll.lng]);
        map.removeLayer(layer);
        
        try{
            const ec = JSON.parse(path.coordinates);
            currentPathCoords = endpoint==='start' ? [...newCoords.reverse(),...ec] : [...ec,...newCoords];
        }catch(e){currentPathCoords=newCoords;}
        
        let len=0;
        for(let i=0;i<currentPathCoords.length-1;i++) len+=calcDist(currentPathCoords[i][0],currentPathCoords[i][1],currentPathCoords[i+1][0],currentPathCoords[i+1][1]);
        
        document.getElementById('pathId').value=pathId;
        document.getElementById('pathName').value=path.name;
        document.getElementById('pathType').value=path.type||'walkway';
        document.getElementById('pathColor').value=path.color||'#3b82f6';
        document.getElementById('pathWidth').value=path.width||2;
        document.getElementById('pathDescription').value=path.description||'';
        document.getElementById('pathLength').innerText=len.toFixed(2);
        document.getElementById('pathModalTitle').innerText='Extend Footwalk';
        document.getElementById('deletePathBtn').style.display='block';
        document.getElementById('pathModal').classList.remove('hidden');
        document.getElementById('pathModal').classList.add('flex');
        
        window.tempPathCoords = currentPathCoords;
        resetDrawingState();
        showToast('Extension drawn! Save to update.','success');
    });
}

function startBranchDrawing(pathId,segIdx,t){
    resetDrawingState();
    const path = footwalksData.find(f=>f.id==pathId);
    if(!path) return;
    
    let coords;
    try{coords=JSON.parse(path.coordinates);}catch(e){return;}
    if(segIdx>=coords.length-1) return;
    
    const brLat = coords[segIdx][0]+t*(coords[segIdx+1][0]-coords[segIdx][0]);
    const brLng = coords[segIdx][1]+t*(coords[segIdx+1][1]-coords[segIdx][1]);
    
    isDrawing = true;
    document.getElementById('drawPathBtn').classList.add('drawing-mode-active');
    document.getElementById('drawPathBtn').innerHTML='<i class="fas fa-stop mr-2"></i> Stop';
    document.getElementById('drawStatus').innerHTML='<i class="fas fa-code-branch mr-1"></i> BRANCH: '+path.name;
    document.getElementById('drawStatus').className='text-sm text-cyan-700 bg-cyan-100 px-3 py-1 rounded-full';
    
    currentDrawControl = new L.Draw.Polyline(map,{
        shapeOptions:{color:'#06b6d4',weight:5,opacity:0.7},
        showLength:true,metric:true,repeatMode:false
    });
    currentDrawControl.enable();
    window._branchLat = brLat;
    window._branchLng = brLng;
    map.setView([brLat,brLng],19);
    
    map.on('draw:created',function(e){
        const layer = e.layer;
        const newCoords = layer.getLatLngs().map(ll=>[ll.lat,ll.lng]);
        map.removeLayer(layer);
        currentPathCoords = [[brLat,brLng],...newCoords];
        
        let len=0;
        for(let i=0;i<currentPathCoords.length-1;i++) len+=calcDist(currentPathCoords[i][0],currentPathCoords[i][1],currentPathCoords[i+1][0],currentPathCoords[i+1][1]);
        
        document.getElementById('pathId').value='';
        document.getElementById('pathName').value='';
        document.getElementById('pathType').value='walkway';
        document.getElementById('pathColor').value='#3b82f6';
        document.getElementById('pathWidth').value='2';
        document.getElementById('pathDescription').value='';
        document.getElementById('pathLength').innerText=len.toFixed(2);
        document.getElementById('pathModalTitle').innerText='Branch New Footwalk';
        document.getElementById('deletePathBtn').style.display='none';
        document.getElementById('pathModal').classList.remove('hidden');
        document.getElementById('pathModal').classList.add('flex');
        
        window.tempPathCoords = currentPathCoords;
        resetDrawingState();
        showToast('Branch drawn! Fill details and save.','success');
    });
}

function calcDist(lat1,lng1,lat2,lng2){
    const R=6371000,dLat=(lat2-lat1)*Math.PI/180,dLng=(lng2-lng1)*Math.PI/180;
    const a=Math.sin(dLat/2)**2+Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLng/2)**2;
    return R*2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a));
}

// ============ SAVE PATH ============
document.getElementById('pathForm').addEventListener('submit',async function(e){
    e.preventDefault();
    const coords = window.tempPathCoords||currentPathCoords;
    if(!coords||coords.length<2){showToast('No path coordinates. Draw first!','error');return;}
    
    const pid = document.getElementById('pathId').value;
    const data = {
        name:document.getElementById('pathName').value,
        type:document.getElementById('pathType').value,
        color:document.getElementById('pathColor').value,
        width:parseFloat(document.getElementById('pathWidth').value),
        description:document.getElementById('pathDescription').value,
        coordinates:JSON.stringify(coords)
    };
    if(pid) data.id = pid;
    if(!data.name){showToast('Enter a path name','error');return;}
    
    try{
        let url, method;
        
        if (pid) {
            // Update existing footwalk
            url = '{{ route("footwalks.update", "") }}/' + pid;
            method = 'PUT';
        } else {
            // Create new footwalk
            url = '{{ route("footwalks.index") }}';
            method = 'POST';
        }
        
        const resp = await fetch(url,{method,headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(data)});
        const result = await resp.json();
        if(result.success){
            showToast(pid?'Footwalk updated!':'Footwalk saved!','success');
            closePathModal();
            await loadFootwalks();
            recalculateConnections();
        }else{
            showToast('Error: '+(result.message||'Unknown'),'error');
        }
    }catch(err){
        console.error(err);
        showToast('Error saving footwalk','error');
    }
});

// ============ LOAD FOOTWALKS ============
async function loadFootwalks(){
    try{
        const r = await fetch('{{ route("footwalks.index") }}');
        const d = await r.json();
        if(d.success&&d.data){footwalksData=d.data;displayFootwalksList(d.data);redrawAllFootwalks();showAllPathMarkers();findAndDisplayConnections();}
    }catch(e){console.error(e);}
}

function redrawAllFootwalks(){
    Object.values(drawnPaths).forEach(l=>map.removeLayer(l));
    drawnPaths={};
    footwalksData.forEach(f=>drawFootwalkOnMap(f));
}

function drawFootwalkOnMap(fw){
    try{
        const c=JSON.parse(fw.coordinates),ll=c.map(c=>[c[0],c[1]]);
        const line=L.polyline(ll,{color:fw.color||'#3b82f6',weight:(fw.width||2)*2,opacity:0.8}).addTo(map);
        line.bindPopup(`<div class="p-2 min-w-[180px]"><b>${fw.name}</b><br><span class="text-xs">${fw.type||'walkway'} | ${fw.width||2}m</span><br><div class="flex flex-wrap gap-1 mt-2"><button onclick="editFootwalk('${fw.id}')" class="bg-blue-500 text-white px-2 py-1 rounded text-xs">Edit</button><button onclick="startExtendDrawing('${fw.id}','start')" class="extend-btn text-xs">Ext Start</button><button onclick="startExtendDrawing('${fw.id}','end')" class="extend-btn text-xs">Ext End</button><button onclick="deleteFootwalkItem('${fw.id}')" class="bg-red-500 text-white px-2 py-1 rounded text-xs">Del</button></div></div>`);
        drawnPaths[fw.id]=line;
    }catch(e){}
}

function showAllPathMarkers(){
    endpointMarkers.forEach(m=>map.removeLayer(m));endpointMarkers=[];
    midpointMarkers.forEach(m=>map.removeLayer(m));midpointMarkers=[];
    if(!showAllMarkersFlag){document.getElementById('markerBtnText').innerText='Show Markers';return;}
    document.getElementById('markerBtnText').innerText='Hide Markers';
    footwalksData.forEach(fw=>{
        try{
            const c=JSON.parse(fw.coordinates);if(c.length<1)return;
            [{p:c[0],type:'start'},{p:c[c.length-1],type:'end'}].forEach(ep=>{
                const icon=L.divIcon({html:'<div class="endpoint-marker"></div>',iconSize:[16,16],className:''});
                const m=L.marker([ep.p[0],ep.p[1]],{icon}).addTo(map);
                m.bindPopup(`<b>${fw.name}</b><br>${ep.type}<br><button onclick="startExtendDrawing('${fw.id}','${ep.type}')" class="extend-btn mt-1">Extend</button>`);
                m.on('click',()=>startExtendDrawing(fw.id,ep.type));endpointMarkers.push(m);
            });
            for(let i=0;i<c.length-1;i++){
                const mlat=(c[i][0]+c[i+1][0])/2,mlng=(c[i][1]+c[i+1][1])/2;
                const icon=L.divIcon({html:'<div class="midpoint-marker"></div>',iconSize:[14,14],className:''});
                const m=L.marker([mlat,mlng],{icon}).addTo(map);
                m.bindPopup(`<b>${fw.name}</b><br>Branch<br><button onclick="startBranchDrawing('${fw.id}',${i},0.5)" class="branch-btn mt-1">Branch</button>`);
                m.on('click',()=>startBranchDrawing(fw.id,i,0.5));midpointMarkers.push(m);
            }
        }catch(e){}
    });
}

function toggleAllMarkers(){showAllMarkersFlag=!showAllMarkersFlag;showAllPathMarkers();}

function findAndDisplayConnections(){
    connectionMarkers.forEach(m=>map.removeLayer(m));connectionMarkers=[];
    const cd=parseFloat(document.getElementById('connectionDistance').value)||5;const conns=[];
    for(let i=0;i<footwalksData.length;i++){for(let j=i+1;j<footwalksData.length;j++){let c1,c2;try{c1=JSON.parse(footwalksData[i].coordinates);c2=JSON.parse(footwalksData[j].coordinates);}catch(e){continue;}const cl=findClosest(c1,c2);if(cl.distance<cd&&cl.point){conns.push({p1:footwalksData[i].name,p2:footwalksData[j].name,pt:cl.point,d:cl.distance});const icon=L.divIcon({html:'<div style="background:#ff9800;width:10px;height:10px;border-radius:50%;border:2px solid white;"></div>',iconSize:[14,14]});const m=L.marker([cl.point.lat,cl.point.lng],{icon}).addTo(map);m.bindPopup(`<b>Link</b><br>${footwalksData[i].name} \u2194 ${footwalksData[j].name}<br>${cl.distance.toFixed(1)}m`);connectionMarkers.push(m);}}}
    const cont=document.getElementById('connectionsList');if(cont)cont.innerHTML=conns.length?conns.map(c=>`<div class="bg-green-50 rounded-lg p-2 text-sm"><i class="fas fa-link text-green-500 mr-2"></i><b>${c.p1}</b> \u2194 <b>${c.p2}</b><span class="text-xs block">${c.d.toFixed(1)}m</span></div>`).join(''):'<div class="text-center text-gray-500 p-4">No connections</div>';
    document.getElementById('connectionCount').innerText=conns.length;
}

function findClosest(c1,c2){let md=Infinity,bp=null;for(let i=0;i<c1.length-1;i++)for(let j=0;j<c2.length-1;j++)for(let t1=0;t1<=10;t1++)for(let t2=0;t2<=10;t2++){const la1=c1[i][0]+(t1/10)*(c1[i+1][0]-c1[i][0]),ln1=c1[i][1]+(t1/10)*(c1[i+1][1]-c1[i][1]),la2=c2[j][0]+(t2/10)*(c2[j+1][0]-c2[j][0]),ln2=c2[j][1]+(t2/10)*(c2[j+1][1]-c2[j][1]),d=calcDist(la1,ln1,la2,ln2);if(d<md){md=d;bp={lat:(la1+la2)/2,lng:(ln1+ln2)/2};}}return{distance:md,point:bp};}
function recalculateConnections(){findAndDisplayConnections();showToast('Connections recalculated!','success');}
function showAllConnections(){if(!connectionMarkers.length){showToast('None','info');return;}const b=L.latLngBounds();connectionMarkers.forEach(m=>b.extend(m.getLatLng()));if(b.isValid()){map.fitBounds(b,{padding:[50,50]});}}

function displayFootwalksList(fw){document.getElementById('pathsList').innerHTML=fw.length?fw.map(f=>`<div class="bg-gray-50 rounded-lg p-3 list-item" id="path-item-${f.id}"><div class="flex justify-between"><div class="flex-1"><div class="flex items-center"><div class="w-3 h-3 rounded-full mr-2" style="background:${f.color||'#3b82f6'}"></div><h4 class="font-semibold text-sm">${f.name}</h4></div><p class="text-xs text-gray-500">${f.type||'walkway'} | ${f.width||2}m</p></div><div class="flex gap-1"><button onclick="editFootwalk('${f.id}')" class="text-blue-600 p-1"><i class="fas fa-edit"></i></button><button onclick="deleteFootwalkItem('${f.id}')" class="text-red-600 p-1"><i class="fas fa-trash"></i></button></div></div></div>`).join(''):'<div class="text-center text-gray-400 p-4">No footwalks</div>';}

async function editFootwalk(id){
    try{
        const r = await fetch('{{ route("footwalks.show", "") }}/' + id);
        const d = await r.json();
        if(d.success){
            const f=d.data;
            document.getElementById('pathId').value=f.id;
            document.getElementById('pathName').value=f.name||'';
            document.getElementById('pathType').value=f.type||'walkway';
            document.getElementById('pathColor').value=f.color||'#3b82f6';
            document.getElementById('pathWidth').value=f.width||2;
            document.getElementById('pathDescription').value=f.description||'';
            let c=[];
            try{c=JSON.parse(f.coordinates);}catch(e){}
            let len=0;
            for(let i=0;i<c.length-1;i++)len+=calcDist(c[i][0],c[i][1],c[i+1][0],c[i+1][1]);
            document.getElementById('pathLength').innerText=len.toFixed(2);
            window.tempPathCoords=c;
            document.getElementById('pathModalTitle').innerText='Edit Footwalk';
            document.getElementById('deletePathBtn').style.display='block';
            document.getElementById('pathModal').classList.remove('hidden');
            document.getElementById('pathModal').classList.add('flex');
            if(c.length)map.fitBounds(L.latLngBounds(c.map(c=>[c[0],c[1]])),{padding:[50,50]});
        }
    }catch(e){
        console.error(e);
    }
}

function deleteFootwalkItem(id){if(confirm('Delete this footwalk?'))deleteFootwalk(id);}

async function deleteFootwalk(id){
    try{
        const el=document.getElementById(`path-item-${id}`);
        if(el)el.classList.add('deleting');
        if(drawnPaths[id]){map.removeLayer(drawnPaths[id]);delete drawnPaths[id];}
        const r=await fetch('{{ route("footwalks.delete", "") }}/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrf
            }
        });
        const d=await r.json();
        if(d.success){
            footwalksData=footwalksData.filter(f=>f.id!=id);
            displayFootwalksList(footwalksData);
            showAllPathMarkers();
            findAndDisplayConnections();
            showToast('Deleted','success');
        }else{await loadFootwalks();}
    }catch(e){await loadFootwalks();}
}

function deleteCurrentPath(){const id=document.getElementById('pathId').value;if(id&&confirm('Delete?')){deleteFootwalk(id);closePathModal();}}

function closePathModal(){document.getElementById('pathModal').classList.add('hidden');document.getElementById('pathModal').classList.remove('flex');window.tempPathCoords=null;}
function resetMapView(){map.setView([9.853,122.890],17);}
function switchTab(tab){['offices','paths','connections'].forEach(t=>{document.getElementById(`${t}Tab`).classList.add('hidden');document.getElementById(`tab${t.charAt(0).toUpperCase()+t.slice(1)}Btn`).classList.remove('tab-active');});document.getElementById(`${tab}Tab`).classList.remove('hidden');document.getElementById(`tab${tab.charAt(0).toUpperCase()+t.slice(1)}Btn`).classList.add('tab-active');if(tab==='connections')findAndDisplayConnections();}
document.addEventListener('keydown',e=>{if(e.key==='Escape'){closeOfficeModal();closePathModal();if(isDrawing)resetDrawingState();}});
document.getElementById('officeModal').addEventListener('click',function(e){if(e.target===this)closeOfficeModal();});
document.getElementById('pathModal').addEventListener('click',function(e){if(e.target===this)closePathModal();});
function showToast(m,t){const ex=document.querySelector('.toast');if(ex)ex.remove();const toast=document.createElement('div');toast.className=`toast toast-${t}`;toast.innerHTML=`<i class="fas fa-${t==='success'?'check-circle':t==='error'?'exclamation-circle':'info-circle'} mr-2"></i>${m}`;document.body.appendChild(toast);setTimeout(()=>{toast.style.opacity='0';toast.style.transform='translateX(100px)';toast.style.transition='all 0.3s ease';setTimeout(()=>toast.remove(),300);},3000);}

document.addEventListener('DOMContentLoaded',function(){
    initMap();
    document.getElementById('searchOffice').addEventListener('keyup',function(e){const t=e.target.value.toLowerCase();displayOfficesList(officesData.filter(o=>o.name.toLowerCase().includes(t)||o.building.toLowerCase().includes(t)));});
    document.getElementById('searchPath').addEventListener('keyup',function(e){const t=e.target.value.toLowerCase();displayFootwalksList(footwalksData.filter(f=>f.name.toLowerCase().includes(t)));});
    document.getElementById('connectionDistance').addEventListener('change',function(){recalculateConnections();});
});
</script>
</body>
</html>