<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

$config = require __DIR__ . '/config.php';

$db_host = $config['db_host'];
$db_user = $config['db_user'];
$db_pass = $config['db_pass'];
$db_name = $config['db_name'];

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$date = $_GET['date'] ?? date('Y-m-d');

$stmt = $conn->prepare("
    SELECT ph.habit_id, ph.habit_type, TIME_FORMAT(ph.habit_time, '%H:%i') AS time, p.name AS pet_name
    FROM pet_habits ph
    JOIN pets p ON ph.pet_id = p.pet_id
    WHERE DATE(ph.habit_time) = ?
");
if (!$stmt) {
    echo json_encode(["error" => "Query error: " . $conn->error]);
    exit;
}
$stmt->bind_param("s", $date);
$stmt->execute();

$result = $stmt->get_result();
$habits = [];

while ($row = $result->fetch_assoc()) {
    $habits[] = $row;
}

echo json_encode($habits);
