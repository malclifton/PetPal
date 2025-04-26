<?php
header('Content-Type: application/json');
session_start();

$testing = isset($_GET['testing']) && $_GET['testing'] === 'true';

if (!isset($_SESSION["user_id"])) {
    echo "Not logged in";
    exit;
}

$config = require __DIR__ . '/config.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

if ($conn->connect_error) {
    echo "Database connection failed";
    exit;
}

// Grab GET parameters
$pet_id = $_GET["pet_id"] ?? null;
$date = $_GET["date"] ?? null;

// If both pet_id and date are missing, it's a bad request
if (!$pet_id && !$date) {
    echo "Invalid request: missing pet_id and date.";
    exit;
}

if ($pet_id) {
    // If pet_id is provided, fetch habits for that specific pet
    $stmt = $conn->prepare("SELECT * FROM pet_habits WHERE pet_id = ?");
    $stmt->bind_param("i", $pet_id);
} else {
    // Otherwise, fetch habits for the given date
    $stmt = $conn->prepare("SELECT * FROM pet_habits WHERE DATE(habit_time) = ?");
    $stmt->bind_param("s", $date);
}

$stmt->execute();
$result = $stmt->get_result();

$habits = [];
while ($row = $result->fetch_assoc()) {
    $habits[] = $row;
}

echo json_encode($habits);
?>
