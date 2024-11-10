<!DOCTYPE html>
<html>
<head>
    <title>NYC Food Truck Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://use.typekit.net/fxn5znb.css">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            background: #fee5d0;
            font-family: Arial, sans-serif;
        }

        /* Container to stack elements vertically */
        .main-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        /* Navbar styling */
        .navbar {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: #fcc598;
            color: white;
        }

       /* Main container with flex layout */
        .map-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
            gap: 20px;
        }

        /* Food truck list styling */
        .food-truck-list {
            flex: 1;
            max-width: 300px;
            background-color: #fcc598;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            color: #333;
            height: 500px; /* Same height as the map */
            overflow-y: auto; /* Enable scrolling if the list is long */
        }

        /* Title for the food truck list */
        .food-truck-list h2 {
            text-align: center;
            color: #fff;
            font-size: 1.5em;
            margin-bottom: 15px;
        }

        /* Individual food truck list items */
        .food-truck-item {
            padding: 10px;
            margin-bottom: 10px;
            background-color: #ffdab9;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        /* Hover effect for list items */
        .food-truck-item:hover {
            cursor: pointer;
            background-color: #e6735e;
            color: #fff;
            transform: scale(1.02);
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Map styling */
        #map {
            flex: 2;
            height: 500px;
            width: 100%;
            min-width: 600px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        }


    </style>
</head>
<body>
    <!-- Main container to align elements vertically -->
    <div class="main-container">
        <nav class="navbar">
            <div class="logo-container">
                <a href="../index.html" class="logo"></a>
            </div>
            <a href="./php/login.php" class="login-button">Log in</a>
            <a href="./php/signup.php" class="signin-button">Sign up</a>
            <a href="logout.php" class="login-button">Log Out</a>
        </nav>  

        <h1 class="title">Find Food Trucks in NYC</h1>
        <div class="map-container">
            <!-- Food truck list on the left side -->
            <div id="food-truck-list" class="food-truck-list"></div>

            <!-- Map on the right side -->
            <div id="map"></div>
</div>

    </div>

    <?php
    include ("./includes/conn.php");
    session_start();
    // Check if admin is logged in and has associated food truck
    if (!isset($_SESSION['id'])) {
        header("Location: login.php");
        exit();
    }

    // Get user ID from session
    $userId = $_SESSION['id'];

    // Fetch user's preferred cuisines
    $userCuisineSql = "SELECT tags FROM userdata WHERE id = ?";
    $userStmt = $conn->prepare($userCuisineSql);
    $userStmt->bind_param("i", $userId);
    $userStmt->execute();
    $userCuisineResult = $userStmt->get_result();
    $userCuisines = $userCuisineResult->fetch_assoc()['tags'];
    $userCuisineList = explode(", ", $userCuisines);

    // Query to fetch food truck data
    $sql = "SELECT id, name, tags, des, logo, hours, latitude AS lat, longitude AS lng FROM foodtruckinfo";
    $result = $conn->query($sql);

    // Initialize an array to store food truck data
    $preferredTrucks = [];
    $otherTrucks = [];
    

   // Process each food truck
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $truckTags = explode(", ", $row['tags']);

            // Check for a match between user's preferences and food truck tags
            if (count(array_intersect($userCuisineList, $truckTags)) > 0) {
                $preferredTrucks[] = $row;
            } else {
                $otherTrucks[] = $row;
            }
        }
    }

    // Merge preferred trucks first, followed by others
    $sortedFoodTrucks = array_merge($preferredTrucks, $otherTrucks);

    // Encode the sorted list as JSON for the frontend
    //echo json_encode($sortedFoodTrucks);

    

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
    const foodTrucks = <?php echo json_encode($sortedFoodTrucks); ?>;

    // Function to fetch address using reverse geocoding API
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
            .catch((error) => {
                console.error('Fetch error:', error);
                callback("Error fetching address.");
            });
    }

    // Add food truck markers to the map
    foodTrucks.forEach((truck) => {
        const marker = L.marker([truck.lat, truck.lng], { icon: foodTruckIcon }).addTo(map);
        fetchAddress(truck.lat, truck.lng, (address) => {
            marker.bindPopup(`<b>${truck.name}</b><br>${truck.des}<br>Cuisines: ${truck.tags}<br>Address: ${address}`);
        });
    });

    // Enable user geolocation
    map.locate({ setView: true, maxZoom: 16 });
    map.on('locationfound', (e) => {
        L.marker(e.latlng).addTo(map).bindPopup("You are here").openPopup();
    });

    // Populate the list of food trucks
    function populateFoodTruckList() {
        const listContainer = document.getElementById('food-truck-list');
        listContainer.innerHTML = '<h2>Food Trucks Near You</h2>';

        foodTrucks.forEach((truck) => {
            const listItem = document.createElement('div');
            listItem.className = 'food-truck-item';
            listItem.textContent = `${truck.name} - ${truck.tags}`;

            // Scroll map to the marker on click
            listItem.addEventListener('click', () => {
                map.setView([truck.lat, truck.lng], 16);
            });

            listContainer.appendChild(listItem);
        });
    }

    // Call the function to populate the list
    populateFoodTruckList();
</script>

</body>
</html>
