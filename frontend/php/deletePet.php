<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

// Manually parse the raw input for DELETE requests
parse_str(file_get_contents("php://input"), $delete_vars);

$pet_id = isset($_POST["id"]) ? (int)$_POST["id"] : 0;
$owner_id = $_SESSION["user_id"];

if ($pet_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid pet ID."]);
    exit;
}

$config = require __DIR__ . '/config.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Delete pet only if it belongs to the logged-in user
$stmt = $conn->prepare("DELETE FROM pets WHERE pet_id = ? AND owner_id = ?");
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("ii", $pet_id, $owner_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Pet deleted successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to delete pet."]);
}

$stmt->close();
$conn->close();
