<?php
header("Content-Type: application/json");
session_start();

$config = require __DIR__ . '/config.php';

$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed"]);
    exit;
}

$habit_id = $_GET["habit_id"];
$habit_type = $_POST["habit_type"];
$habit_description = $_POST["habit_description"];
$habit_time = $_POST["habit_time"];

$stmt = $conn->prepare("UPDATE pet_habits SET habit_type = ?, habit_description = ?, habit_time = ? WHERE habit_id = ?");
$stmt->bind_param("sssi", $habit_type, $habit_description, $habit_time, $habit_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => $conn->error]);
}
