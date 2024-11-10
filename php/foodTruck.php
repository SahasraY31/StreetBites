<?php
// Include the database connection file
include 'conn.php';

// cURL request to fetch food truck data
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://mollieswenson.com/api/v1/foodtruck",
    CURLOPT_RETURNTRANSFER => true
]);
$response = curl_exec($curl);
curl_close($curl);

// Decode JSON response
$data = json_decode($response, true);

if (is_array($data)) {
    foreach ($data as $foodTruck) {
        // Prepare and bind the SQL statement
        $stmt = $conn->prepare("INSERT INTO foodtruckinfo (name, tags, des, current_location, logo, hours, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sssssss", 
            $foodTruck['name'],
            $foodTruck['tags'],
            $foodTruck['des'],
            $foodTruck['current_location'],
            $foodTruck['logo'],
            $foodTruck['hours'],
            $foodTruck['longitude'],
            $foodTruck['latitude']
        );

        // Execute the statement
        $stmt->execute();
    }
    echo "Data inserted successfully!";
} else {
    echo "Failed to fetch or decode data.";
}

$conn->close();
?>
