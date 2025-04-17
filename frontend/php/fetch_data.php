<?php
session_start();

$config = require __DIR__ . '/config.php';

$db_host = $config['db_host'];
$db_user = $config['db_user'];
$db_pass = $config['db_pass'];
$db_name = $config['db_name'];

// Create a connection to the database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get logged-in user's ID
$owner_id = $_SESSION['user_id'] ?? null;

if (!$owner_id) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

// Prepare SQL statement to get only the current owner's pets
$sql = "SELECT name, owner_id FROM pets WHERE owner_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["error" => "Query error: " . $conn->error]);
    exit;
}

$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();

// Create an array to store the data
$data = array();

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Return the data as JSON
echo json_encode($data);

// Close connections
$stmt->close();
$conn->close();
