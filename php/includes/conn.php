<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "streetbites";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  echo "Failed to connect to MySQL: " . $conn->connect_error;
}
?>