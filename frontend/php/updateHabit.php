<?php
header('Content-Type: application/json');
session_start();

$testing = isset($_GET['testing']) && $_GET['testing'] === 'true';

$config = require __DIR__ . '/config.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

if ($conn->connect_error) {
    if ($testing) {
        echo "Database connection failed";
        exit;
    } else {
        echo json_encode(["success" => false, "message" => "Connection failed"]);
        exit;
    }
}

$habit_id = $_POST["habit_id"] ?? '';
$habit_type = $_POST["habit_type"] ?? '';
$habit_description = $_POST["habit_description"] ?? '';
$habit_time = $_POST["habit_time"] ?? '';

$stmt = $conn->prepare("UPDATE pet_habits SET habit_type = ?, habit_description = ?, habit_time = ? WHERE habit_id = ?");
$stmt->bind_param("sssi", $habit_type, $habit_description, $habit_time, $habit_id);

if ($stmt->execute()) {
    if ($testing) {
        echo "success";
        exit;
    } else {
        echo json_encode(["success" => true]);
    }
} else {
    if ($testing) {
        echo "update failed";
        exit;
    } else {
        echo json_encode(["success" => false, "message" => $conn->error]);
    }
}
?>
