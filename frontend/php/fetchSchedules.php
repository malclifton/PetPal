<?php
header('Content-Type: application/json');
session_start();

$config = require __DIR__ . '/config.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed"]);
    exit;
}

$selectedDate = $_GET['date'] ?? date('Y-m-d');

// Get currently logged-in owner's ID
$owner_id = $_SESSION['user_id'] ?? null;

if (!$owner_id) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

// Query to fetch tasks including repeat logic and pet name, filtered by owner_id
$sql = "
    SELECT s.task, s.scheduled_time, s.repeat_frequency, s.pet_id, p.name AS pet_name
    FROM schedules s
    JOIN pets p ON s.pet_id = p.pet_id
    WHERE 
        p.owner_id = ? AND (
            DATE(s.scheduled_time) = ? OR
            (s.repeat_frequency = 'daily') OR
            (s.repeat_frequency = 'weekly' AND DAYOFWEEK(s.scheduled_time) = DAYOFWEEK(?)) OR
            (s.repeat_frequency = 'monthly' AND DAY(s.scheduled_time) = DAY(?))
        )
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "Query error: " . $conn->error]);
    exit;
}
$stmt->bind_param("ssss", $owner_id, $selectedDate, $selectedDate, $selectedDate);
$stmt->execute();
$result = $stmt->get_result();

$tasks = [];
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}

echo json_encode($tasks);
