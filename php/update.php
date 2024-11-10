<?php
session_start();
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['foodtruck_name'])) {
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
if ($data['truckName'] !== $_SESSION['foodtruck_name']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include ("./includes/conn.php");

// Log the update attempt
error_log("Updating location for truck: " . $data['truckName'] . " to lat: " . $data['latitude'] . ", lng: " . $data['longitude']);

// Update the location in the database
$stmt = $conn->prepare("UPDATE foodtruckinfo SET latitude = ?, longitude = ? WHERE name = ?");
$stmt->bind_param("dds", $data['latitude'], $data['longitude'], $data['truckName']);

if ($stmt->execute()) {
    error_log("Location update successful for truck: " . $data['truckName']);
    echo json_encode(['success' => true]);
} else {
    error_log("Location update failed for truck: " . $data['truckName'] . ". Error: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>