<!DOCTYPE html>
<html>
<head>
    <title>NYC Food Truck Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map { height: 500px; width: 100%; }
    </style>
</head>
<body>
    <h3>Find Food Trucks in NYC</h3>
    <div id="map"></div>

    <?php
    include ("./includes/conn.php");

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
            iconUrl: '../designs/streetbites_circle_logo.PNG', // Local file path
            iconSize: [50, 50], // Adjust size
            iconAnchor: [20, 40], // Position properly
            popupAnchor: [0, -35] // Offset for pop-up
        });

        // PHP-generated JavaScript array of food trucks
        const foodTrucks = <?php echo json_encode($foodTrucks); ?>;

        function fetchAddress(lat, lng, callback) {
            const apiUrl = `https://geocode.maps.co/reverse?lat=${lat}&lon=${lng}&api_key=6730042c165a6904393345twyf9dd61`;
            fetch(apiUrl)
                .then(response => {
                    console.log('API response:', response); // Log the response object
                    if (response.ok) {
                        return response.json();
                    } else {
                        throw new Error('API request failed');
                    }
                })
                .then(data => {
                    console.log('API data:', data); // Log the data
                    if (data && data.display_name) {
                        callback(data.display_name);
                    } else {
                        callback("Address not found.");
                    }
                })
                .catch((error) => {
                    console.error('Fetch error:', error); // Log the error
                    callback("Error fetching address.");
                });
        }


        // Add food truck markers with the custom local icon
        foodTrucks.forEach((truck) => {
            const marker = L.marker([truck.lat, truck.lng], { icon: foodTruckIcon }).addTo(map);
            fetchAddress(truck.lat, truck.lng, (address) => {
                marker.bindPopup(`<b>${truck.name}</b><br>${truck.des}<br>Cuisines: ${truck.tags}<br>Address: ${address}`);
            });
        });

        // Optional: User Geolocation
        map.locate({ setView: true, maxZoom: 16 });
        map.on('locationfound', (e) => {
            L.marker(e.latlng).addTo(map).bindPopup("You are here").openPopup();
        });
    </script>
</body>
</html>
