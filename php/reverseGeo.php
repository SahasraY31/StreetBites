<?php
// Include database connection
include('./includes/conn.php');

// Function to get the address from latitude and longitude
function getAddress($latitude, $longitude) {
    $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat=$latitude&lon=$longitude";
    $response = file_get_contents($url);

    if ($response === FALSE) {
        return "Error fetching address.";
    }

    $data = json_decode($response, true);

    if (isset($data['display_name'])) {
        return $data['display_name'];
    } else {
        return "Address not found.";
    }
}

// Check if latitude and longitude are provided
if (isset($_GET['lat']) && isset($_GET['lng'])) {
    $latitude = $_GET['lat'];
    $longitude = $_GET['lng'];
    $address = getAddress($latitude, $longitude);
    echo $address;
} else {
    echo "Invalid coordinates.";
}
?>
