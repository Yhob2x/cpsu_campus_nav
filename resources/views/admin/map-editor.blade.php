<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Map Editor | CPSU Navigator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        #map { height: 500px; border-radius: 8px; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-96 bg-white shadow-lg overflow-y-auto">
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-6">🗺️ Map Editor</h2>
                
                <form id="officeForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Office Name</label>
                        <input type="text" name="name" required class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Building</label>
                        <input type="text" name="building" required class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Floor/Room</label>
                        <input type="text" name="room_number" placeholder="e.g., 2nd Floor, Room 201" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Category</label>
                        <select name="category" class="w-full border rounded-lg px-3 py-2">
                            <option value="Administrative">Administrative</option>
                            <option value="Academic">Academic</option>
                            <option value="Facility">Facility</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Working Hours</label>
                        <input type="text" name="working_hours" placeholder="8:00 AM - 5:00 PM" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Contact Number</label>
                        <input type="text" name="contact_number" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input type="email" name="email" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Latitude</label>
                            <input type="text" name="lat" id="lat" readonly class="w-full border rounded-lg px-3 py-2 bg-gray-50">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Longitude</label>
                            <input type="text" name="lng" id="lng" readonly class="w-full border rounded-lg px-3 py-2 bg-gray-50">
                        </div>
                    </div>
                    <div class="text-sm text-gray-500">
                        💡 Click on the map to place the marker
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                        Save Office
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Map -->
        <div class="flex-1 p-4">
            <div id="map"></div>
            <div class="mt-4 text-sm text-gray-600 text-center">
                Click anywhere on the map to add an office location
            </div>
        </div>
    </div>

    <script>
        let map;
        let currentMarker = null;
        
        function initMap() {
            map = L.map('map').setView([10.4605, 122.9336], 17);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            // Load existing offices
            loadOffices();
            
            // Handle map click
            map.on('click', function(e) {
                const { lat, lng } = e.latlng;
                
                if (currentMarker) {
                    map.removeLayer(currentMarker);
                }
                
                currentMarker = L.marker([lat, lng]).addTo(map)
                    .bindPopup(`Selected Location<br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`)
                    .openPopup();
                
                document.getElementById('lat').value = lat.toFixed(8);
                document.getElementById('lng').value = lng.toFixed(8);
            });
        }
        
        async function loadOffices() {
            try {
                const response = await fetch('/api/offices');
                const data = await response.json();
                
                if (data.success && data.data) {
                    data.data.forEach(office => {
                        if (office.lat && office.lng) {
                            L.marker([office.lat, office.lng])
                                .addTo(map)
                                .bindPopup(`<b>${office.name}</b><br>${office.building}`);
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading offices:', error);
            }
        }
        
        document.getElementById('officeForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);
            
            if (!data.lat || !data.lng) {
                alert('Please click on the map to select a location');
                return;
            }
            
            try {
                const response = await fetch('/api/offices', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Office saved successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error saving office');
            }
        });
        
        initMap();
    </script>
</body>
</html>