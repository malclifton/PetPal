<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}


$config = require __DIR__ . '/config.php';

$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

$task = $_POST['task'] ?? '';
$scheduled_time = $_POST['scheduled_time'] ?? '';
$repeat_frequency = $_POST['repeat_frequency'] ?? '';
$pet_id = isset($_POST['pet_id']) ? (int)$_POST['pet_id'] : null;
$owner_id = $_SESSION['user_id'] ?? null;

if (!$task || !$scheduled_time || !$repeat_frequency || !$pet_id || !$owner_id) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

$sql = "INSERT INTO schedules (task, scheduled_time, repeat_frequency, pet_id, owner_id) 
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("sssii", $task, $scheduled_time, $repeat_frequency, $pet_id, $owner_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Task saved successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Error saving task: " . $stmt->error]);
}
