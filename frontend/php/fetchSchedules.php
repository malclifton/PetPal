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

$sql = "
    SELECT s.task, s.scheduled_time, s.repeat_frequency, s.pet_id, p.name AS pet_name
    FROM schedules s
    JOIN pets p ON s.pet_id = p.pet_id
    WHERE 
        DATE(s.scheduled_time) = ? OR
        (s.repeat_frequency = 'daily') OR
        (s.repeat_frequency = 'weekly' AND DAYOFWEEK(s.scheduled_time) = DAYOFWEEK(?)) OR
        (s.repeat_frequency = 'monthly' AND DAY(s.scheduled_time) = DAY(?))
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "Query error: " . $conn->error]);
    exit;
}
$stmt->bind_param("sss", $selectedDate, $selectedDate, $selectedDate);
$stmt->execute();
$result = $stmt->get_result();

$tasks = [];
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}

echo json_encode($tasks);
