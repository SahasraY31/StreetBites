<?php
// Include database connection
include('./includes/conn.php');  // Assuming you have a conn.php for DB connection

// Function to fetch all food truck data using cURL with JWT authorization
function getFoodTruckData() {
    // URL for the API
    $url = "https://mollieswenson.com/api/v1/foodtruck";  // API endpoint
    
    // JWT token for authentication
    $jwtToken = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjVhMTYxNDZlMTNkZjNlMTUxYWEzZjQyZSIsImlhdCI6MTUxMTM5NjY5NywiZXhwIjoxNTEzOTg4Njk3fQ.0YB9WlPxTdfMb5ysZO1qKRjrOgeHKmLVqVxfbkb4gTo"; // Provided JWT token

    // Initialize cURL session
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Set timeout to 60 seconds
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $jwtToken" // Pass the JWT token in the Authorization header
    ]);
    curl_setopt($ch, CURLOPT_VERBOSE, true); // Enable verbose output for debugging
    curl_setopt($ch, CURLOPT_STDERR, fopen('php://stderr', 'w')); // Log errors to stderr

    // Execute the cURL request
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);  // Display cURL error
        return false; // Return false if cURL fails
    }
    // Print the raw response to verify if it's returning data
    echo "<pre>";
    print_r($response); // Print the raw API response
    echo "</pre>";
    
    // Close cURL session
    curl_close($ch);


    // Check if the response is valid JSON
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Error decoding JSON response: " . json_last_error_msg();
        return false; // Return false if JSON decoding fails
    }
    
    // Return the decoded data
    return $data;
}

// Function to insert food truck data into the database
function insertFoodTruckData($conn, $foodTruck) {
    $stmt = $conn->prepare("INSERT INTO foodtruckinfo (name, tags, des, logo, hours, longitude, latitude) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt === false) {
        echo "Error preparing the statement: " . $conn->error;
        return false;
    }

    $stmt->bind_param("sssssss", $foodTruck['name'], $foodTruck['tags'], $foodTruck['description'], $foodTruck['logo'], $foodTruck['hours'], $foodTruck['longitude'], $foodTruck['latitude']);

    if (!$stmt->execute()) {
        echo "Error executing statement: " . $stmt->error;
        return false;
    }

    return true;
}

// Fetch all food truck data
$foodTrucks = getFoodTruckData();

// Check if the data is valid and proceed with insertion
if ($foodTrucks && !empty($foodTrucks)) {
    foreach ($foodTrucks as $foodTruck) {
        // Extract food truck data
        $name = $foodTruck['name'];
        $tags = $foodTruck['foodtype'] ?? NULL;  // If 'foodtype' exists, use it; otherwise, set NULL
        $description = NULL;  // No description in the API response, so it's NULL
        $logo = NULL;  // Assuming no logo in the API response, so it's NULL
        $hours = NULL;  // Assuming no hours in the API response, so it's NULL
        $longitude = $foodTruck['geometry']['coordinates'][0];  // Longitude
        $latitude = $foodTruck['geometry']['coordinates'][1];  // Latitude

        // Insert the food truck data into the database
        if (insertFoodTruckData($conn, [
            'name' => $name,
            'tags' => $tags,
            'description' => $description,
            'logo' => $logo,
            'hours' => $hours,
            'longitude' => $longitude,
            'latitude' => $latitude
        ])) {
            echo "Food truck '{$name}' inserted into the database.<br>";
        } else {
            echo "Failed to insert food truck '{$name}'.<br>";
        }
    }
} else {
    echo "No food trucks found or an error occurred while fetching data.<br>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Insert Food Trucks</title>
</head>
<body>
    <h2>Food Trucks Inserted into Database</h2>
    <p>The food trucks from the API have been successfully inserted into the database.</p>
</body>
</html>














