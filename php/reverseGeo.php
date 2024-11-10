<?php
// Include database connection
include('includes/conn.php');  // Assuming you have a conn.php for DB connection

// GeoCode API Key
$apiKey = '6730042c165a6904393345twyf9dd61';  // Your new API key

// Function to get coordinates (latitude and longitude) from an address
function getCoordinates($address, $apiKey) {
    $encodedAddress = urlencode($address);
    $url = "https://geocode.maps.co/search?q=$encodedAddress&api_key=$apiKey";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (isset($data[0]['lat']) && isset($data[0]['lon'])) {
        return ['latitude' => $data[0]['lat'], 'longitude' => $data[0]['lon']];
    } else {
        return null;
    }
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the user-inputted address
    $address = $_POST['address'];

    // Get latitude and longitude for the address
    $coordinates = getCoordinates($address, $apiKey);

    if ($coordinates) {
        $latitude = $coordinates['latitude'];
        $longitude = $coordinates['longitude'];

        // Output latitude and longitude
        echo "Address: $address<br>";
        echo "Latitude: $latitude<br>";
        echo "Longitude: $longitude<br>";

        // Insert the latitude and longitude into the database (optional)
        // $stmt = $conn->prepare("INSERT INTO locations (address, latitude, longitude) VALUES (?, ?, ?)");
        // $stmt->bind_param("sdd", $address, $latitude, $longitude);
        // $stmt->execute();
    } else {
        echo "Unable to fetch coordinates for the address provided.<br>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Get Coordinates from Address</title>
</head>
<body>
    <h2>Enter Address to Get Coordinates</h2>
    <form method="post">
        <label for="address">Address:</label>
        <input type="text" id="address" name="address" required><br><br>
        <input type="submit" value="Get Coordinates">
    </form>
</body>
</html>
