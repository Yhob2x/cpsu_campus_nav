<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Office Management - CPSU Admin</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: linear-gradient(135deg, #1a2c3e 0%, #0f1a24 100%); color: white; position: fixed; height: 100vh; padding: 20px; overflow-y: auto; }
        .sidebar h2 { font-size: 1.3rem; margin-bottom: 30px; padding-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar nav a { display: block; color: white; text-decoration: none; padding: 12px 15px; margin-bottom: 5px; border-radius: 8px; transition: background 0.3s; }
        .sidebar nav a:hover, .sidebar nav a.active { background: rgba(255,255,255,0.1); }
        
        /* Main Content */
        .main-content { margin-left: 260px; padding: 20px; }
        .header { background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .btn-logout { background: #dc2626; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; }
        .btn-primary { background: #0057a3; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .btn-success { background: #10b981; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; }
        .btn-danger { background: #ef4444; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; }
        .btn-warning { background: #f59e0b; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; }
        
        /* Form Styles */
        .form-modal { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; }
        .form-modal.active { display: flex; }
        .form-container { background: white; border-radius: 16px; width: 90%; max-width: 800px; max-height: 90vh; overflow-y: auto; padding: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #1a2c3e; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        
        /* Table Styles */
        .office-table { background: white; border-radius: 12px; overflow-x: auto; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-weight: 600; color: #374151; }
        tr:hover { background: #f9fafb; }
        
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        
        .map-preview { height: 300px; margin-top: 10px; border-radius: 8px; overflow: hidden; }
        .status-badge { display: inline-block; padding: 4px 8px; border-radius: 20px; font-size: 12px; }
        .status-active { background: #d1fae5; color: #065f46; }
        .status-inactive { background: #fee2e2; color: #991b1b; }
        
        .action-buttons { display: flex; gap: 8px; }
        .edit-btn, .delete-btn { padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 12px; }
        .edit-btn { background: #0057a3; color: white; border: none; }
        .delete-btn { background: #ef4444; color: white; border: none; }
        
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <h2>🏫 CPSU Admin</h2>
        <nav>
            <a href="/admin/dashboard">📊 Dashboard</a>
            <a href="/admin/offices" class="active">🏢 Office Management</a>
            <a href="/admin/processes">📋 Process Management</a>
            <a href="/admin/map-editor">🗺️ Map Editor</a>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="header">
            <div style="display: flex; gap: 15px; align-items: center;">
                <button class="mobile-menu-btn" onclick="toggleSidebar()" style="display: none; background: none; border: none; font-size: 24px; cursor: pointer;">☰</button>
                <h1>Office Management</h1>
            </div>
            <form method="POST" action="{{ url('/logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" class="btn-logout">Logout</button>
            </form>
        </div>
        
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        <button class="btn-primary" onclick="openAddModal()">+ Add New Office</button>
        <br><br>
        
        <div class="office-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Building</th>
                        <th>Category</th>
                        <th>Coordinates</th>
                        <th>Working Hours</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($offices as $office)
                    <tr>
                        <td>{{ $office->office_id }}</td>
                        <td><strong>{{ $office->name }}</strong><br><small>{{ $office->description ? Str::limit($office->description, 50) : '' }}</small></td>
                        <td>{{ $office->building }}<br><small>{{ $office->floor }} - {{ $office->room_number }}</small></td>
                        <td>{{ ucfirst($office->category) }}</td>
                        <td><small>Lat: {{ $office->lat }}<br>Lng: {{ $office->lng }}</small></td>
                        <td><small>{{ $office->working_hours ?: '8:00 AM - 5:00 PM' }}</small></td>
                        <td><span class="status-badge {{ $office->is_active ? 'status-active' : 'status-inactive' }}">{{ $office->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="edit-btn" onclick="openEditModal({{ json_encode($office) }})">Edit</button>
                                <form method="POST" action="{{ route('admin.offices.destroy', $office->office_id) }}" style="display: inline;" onsubmit="return confirm('Delete this office?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-btn">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </div>
            @if($offices->isEmpty())
                <div style="text-align: center; padding: 50px; background: white; border-radius: 12px; margin-top: 20px;">
                    <p>No offices found. Click "Add New Office" to get started.</p>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Add/Edit Modal -->
    <div class="form-modal" id="officeModal">
        <div class="form-container">
            <h2 id="modalTitle">Add New Office</h2>
            <form method="POST" action="" id="officeForm">
                @csrf
                <input type="hidden" name="_method" id="methodField" value="POST">
                <input type="hidden" name="office_id_original" id="officeIdOriginal">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Office ID *</label>
                        <input type="text" name="office_id" id="officeId" required placeholder="e.g., REG001">
                    </div>
                    <div class="form-group">
                        <label>Office Name *</label>
                        <input type="text" name="name" id="name" required placeholder="e.g., Registrar's Office">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="description" rows="3" placeholder="Describe the office services..."></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Building *</label>
                        <input type="text" name="building" id="building" required placeholder="e.g., Main Building">
                    </div>
                    <div class="form-group">
                        <label>Floor</label>
                        <input type="text" name="floor" id="floor" placeholder="e.g., 2nd Floor">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Room Number</label>
                        <input type="text" name="room_number" id="roomNumber" placeholder="e.g., 201">
                    </div>
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category" id="category" required>
                            <option value="">Select Category</option>
                            <option value="academic">Academic</option>
                            <option value="admin">Administrative</option>
                            <option value="facility">Facility</option>
                            <option value="services">Student Services</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Latitude *</label>
                        <input type="number" step="any" name="lat" id="lat" required placeholder="e.g., 9.85325">
                    </div>
                    <div class="form-group">
                        <label>Longitude *</label>
                        <input type="number" step="any" name="lng" id="lng" required placeholder="e.g., 122.88981">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Working Hours *</label>
                    <input type="text" name="working_hours" id="workingHours" placeholder="e.g., 8:00 AM - 5:00 PM, Monday-Friday">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" id="contactNumber" placeholder="e.g., (034) 123-4567">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" id="email" placeholder="e.g., office@cpsu.edu">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="is_active" id="isActive">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                
                <div id="mapPreview" class="map-preview"></div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn-warning" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Save Office</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let previewMap = null;
        let currentMarker = null;
        
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }
        
        function openAddModal() {
            document.getElementById('modalTitle').innerText = 'Add New Office';
            document.getElementById('officeForm').action = '{{ route("admin.offices.store") }}';
            document.getElementById('methodField').value = 'POST';
            document.getElementById('officeForm').reset();
            document.getElementById('officeId').disabled = false;
            closeModal();
            document.getElementById('officeModal').classList.add('active');
            initPreviewMap(9.85325, 122.88981);
        }
        
        function openEditModal(office) {
            document.getElementById('modalTitle').innerText = 'Edit Office';
            document.getElementById('officeForm').action = '/admin/offices/' + office.office_id;
            document.getElementById('methodField').value = 'PUT';
            document.getElementById('officeId').value = office.office_id;
            document.getElementById('officeId').disabled = true;
            document.getElementById('officeIdOriginal').value = office.office_id;
            document.getElementById('name').value = office.name;
            document.getElementById('description').value = office.description || '';
            document.getElementById('building').value = office.building || '';
            document.getElementById('floor').value = office.floor || '';
            document.getElementById('roomNumber').value = office.room_number || '';
            document.getElementById('category').value = office.category || '';
            document.getElementById('lat').value = office.lat;
            document.getElementById('lng').value = office.lng;
            document.getElementById('workingHours').value = office.working_hours || '';
            document.getElementById('contactNumber').value = office.contact_number || '';
            document.getElementById('email').value = office.email || '';
            document.getElementById('isActive').value = office.is_active ? '1' : '0';
            document.getElementById('officeModal').classList.add('active');
            initPreviewMap(office.lat, office.lng);
        }
        
        function initPreviewMap(lat, lng) {
            setTimeout(() => {
                const container = document.getElementById('mapPreview');
                if (container && typeof L !== 'undefined') {
                    if (previewMap) {
                        previewMap.remove();
                    }
                    previewMap = L.map('mapPreview').setView([lat, lng], 18);
                    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                        maxZoom: 20
                    }).addTo(previewMap);
                    
                    if (currentMarker) {
                        previewMap.removeLayer(currentMarker);
                    }
                    currentMarker = L.marker([lat, lng]).addTo(previewMap);
                    
                    // Update marker when lat/lng inputs change
                    const latInput = document.getElementById('lat');
                    const lngInput = document.getElementById('lng');
                    
                    const updateMarker = () => {
                        const newLat = parseFloat(latInput.value);
                        const newLng = parseFloat(lngInput.value);
                        if (!isNaN(newLat) && !isNaN(newLng)) {
                            if (currentMarker) {
                                previewMap.removeLayer(currentMarker);
                            }
                            currentMarker = L.marker([newLat, newLng]).addTo(previewMap);
                            previewMap.setView([newLat, newLng]);
                        }
                    };
                    
                    latInput.removeEventListener('input', updateMarker);
                    lngInput.removeEventListener('input', updateMarker);
                    latInput.addEventListener('input', updateMarker);
                    lngInput.addEventListener('input', updateMarker);
                }
            }, 100);
        }
        
        function closeModal() {
            document.getElementById('officeModal').classList.remove('active');
            if (previewMap) {
                previewMap.remove();
                previewMap = null;
            }
        }
        
        // Close modal when clicking outside
        document.getElementById('officeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Responsive sidebar
        if (window.innerWidth <= 768) {
            document.querySelector('.mobile-menu-btn').style.display = 'block';
        }
        window.addEventListener('resize', () => {
            if (window.innerWidth <= 768) {
                document.querySelector('.mobile-menu-btn').style.display = 'block';
            } else {
                document.querySelector('.mobile-menu-btn').style.display = 'none';
                document.getElementById('sidebar').classList.remove('open');
            }
        });
    </script>
</body>
</html>