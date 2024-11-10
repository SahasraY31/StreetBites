<!-- File 1: adminhome.php -->
<?php
session_start();
// Check if admin is logged in and has associated food truck
if (!isset($_SESSION['admin_truck_name'])) {
    header("Location: login.php");
    exit();
}

include ("./includes/conn.php");
?>
<!DOCTYPE html>
<html>
<head>
    <title>NYC Food Truck Map - Admin</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
        }
        
        h3 {
            color: #333;
        }
        
        #map { 
            height: 500px; 
            width: 100%;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .update-form {
            margin: 20px 0;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .update-form h4 {
            margin-top: 0;
            color: #444;
        }
        
        .update-form input[type="text"] {
            width: 300px;
            padding: 10px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .update-form button {
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .update-form button:hover {
            background: #45a049;
        }
        
        #updateStatus {
            margin-top: 10px;
            padding: 10px;
            border-radius: 4px;
        }
        
        .success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        
        .error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
        
        .processing {
            background-color: #d9edf7;
            color: #31708f;
            border: 1px solid #bce8f1;
        }
    </style>
</head>
<body>
    <h3>Manage Your Food Truck Location</h3>
    
    <div class="update-form">
        <h4>Update <?php echo htmlspecialchars($_SESSION['admin_truck_name']); ?>'s Location</h4>
        <input type="text" id="newAddress" placeholder="Enter new address (e.g., 123 Main St, New York, NY)">
        <button onclick="updateLocation()">Update Location</button>
        <div id="updateStatus"></div>
    </div>

    <div id="map"></div>

    <?php
    // Query to fetch food truck data
    $sql = "SELECT id, name, tags, des, logo, hours, latitude AS lat, longitude AS lng FROM foodtruckinfo";
    $result = $conn->query($sql);

    // Initialize an array to store food truck data
    $foodTrucks = [];

    // Fetch data from the result set
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $foodTrucks[] = $row;
        }
    }

    // Close the database connection
    $conn->close();
    ?>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Initialize the map centered on New York City
        const map = L.map('map').setView([40.7128, -74.0060], 13);

        // Add OSM tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Custom local icon for food trucks
        const foodTruckIcon = L.icon({
            iconUrl: '../designs/streetbites_circle_logo.PNG',
            iconSize: [50, 50],
            iconAnchor: [20, 40],
            popupAnchor: [0, -35]
        });

        // PHP-generated JavaScript array of food trucks
        const foodTrucks = <?php echo json_encode($foodTrucks); ?>;
        const adminTruckName = <?php echo json_encode($_SESSION['admin_truck_name']); ?>;
        let adminMarker = null;
        let markers = new Map(); // Store all markers for easy access

        function fetchAddress(lat, lng, callback) {
            const apiUrl = `https://geocode.maps.co/reverse?lat=${lat}&lon=${lng}&api_key=6730042c165a6904393345twyf9dd61`;
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    if (data && data.display_name) {
                        callback(data.display_name);
                    } else {
                        callback("Address not found.");
                    }
                })
                .catch(() => callback("Error fetching address."));
        }

        // Add food truck markers
        foodTrucks.forEach((truck) => {
            const marker = L.marker([truck.lat, truck.lng], { icon: foodTruckIcon }).addTo(map);
            markers.set(truck.name, marker); // Store marker reference
            
            if (truck.name === adminTruckName) {
                adminMarker = marker;
                // Center map on admin's truck initially
                map.setView([truck.lat, truck.lng], 15);
            }
            
            fetchAddress(truck.lat, truck.lng, (address) => {
                marker.bindPopup(`
                    <b>${truck.name}</b><br>
                    ${truck.des}<br>
                    Cuisines: ${truck.tags}<br>
                    Address: ${address}
                `);
            });
        });

        function updateLocation() {
            const address = document.getElementById('newAddress').value;
            const statusDiv = document.getElementById('updateStatus');
            
            if (!address.trim()) {
                statusDiv.className = 'error';
                statusDiv.innerHTML = 'Please enter an address';
                return;
            }

            statusDiv.className = 'processing';
            statusDiv.innerHTML = 'Processing...';

            // First, geocode the address to get coordinates
            fetch(`https://geocode.maps.co/search?q=${encodeURIComponent(address)}&api_key=6730042c165a6904393345twyf9dd61`)
                .then(response => response.json())
                .then(data => {
                    if (data && data[0]) {
                        const lat = data[0].lat;
                        const lon = data[0].lon;

                        // Now update the database with new coordinates
                        fetch('update_location.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                truckName: adminTruckName,
                                latitude: lat,
                                longitude: lon
                            })
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                statusDiv.className = 'success';
                                statusDiv.innerHTML = 'Location updated successfully!';
                                
                                // Update marker position
                                if (adminMarker) {
                                    adminMarker.setLatLng([lat, lon]);
                                    map.setView([lat, lon], 15);
                                    
                                    // Update popup with new address
                                    fetchAddress(lat, lon, (address) => {
                                        const truck = foodTrucks.find(t => t.name === adminTruckName);
                                        adminMarker.bindPopup(`
                                            <b>${truck.name}</b><br>
                                            ${truck.des}<br>
                                            Cuisines: ${truck.tags}<br>
                                            Address: ${address}
                                        `);
                                    });
                                }
                            } else {
                                statusDiv.className = 'error';
                                statusDiv.innerHTML = 'Error updating location: ' + result.message;
                            }
                        })
                        .catch(error => {
                            statusDiv.className = 'error';
                            statusDiv.innerHTML = 'Error updating location in database';
                            console.error('Error:', error);
                        });
                    } else {
                        statusDiv.className = 'error';
                        statusDiv.innerHTML = 'Could not find coordinates for this address';
                    }
                })
                .catch(error => {
                    statusDiv.className = 'error';
                    statusDiv.innerHTML = 'Error geocoding address';
                    console.error('Error:', error);
                });
        }

        // Optional: User Geolocation
        map.locate({ setView: false, maxZoom: 16 });
        map.on('locationfound', (e) => {
            L.marker(e.latlng).addTo(map)
                .bindPopup("You are here")
                .openPopup();
        });

        // Add keyboard event listener for the address input
        document.getElementById('newAddress').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                updateLocation();
            }
        });
    </script>
</body>
</html>

<!-- File 2: update_location.php -->
<?php
session_start();
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_truck_name'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate input data
if (!isset($data['truckName']) || !isset($data['latitude']) || !isset($data['longitude'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit();
}

// Validate coordinates
if (!is_numeric($data['latitude']) || !is_numeric($data['longitude'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid coordinates']);
    exit();
}

// Verify that the truck name matches the session
if ($data['truckName'] !== $_SESSION['admin_truck_name']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include ("./includes/conn.php");

// Update the location in the database
$stmt = $conn->prepare("UPDATE foodtruckinfo SET latitude = ?, longitude = ? WHERE name = ?");
$stmt->bind_param("dds", $data['latitude'], $data['longitude'], $data['truckName']);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>