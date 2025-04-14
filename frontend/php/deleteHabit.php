<?php
header("Content-Type: application/json");
session_start();

$config = require __DIR__ . '/config.php';

$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed"]);
    exit;
}

$habit_id = $_POST['habit_id'];

$stmt = $conn->prepare("DELETE FROM pet_habits WHERE habit_id = ?");
$stmt->bind_param("i", $habit_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => $conn->error]);
}
