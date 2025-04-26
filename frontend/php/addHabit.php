<?php
header('Content-Type: application/json');
session_start();

$testing = isset($_GET['testing']) && $_GET['testing'] === 'true';

if (!isset($_SESSION["user_id"])) {
    if ($testing) {
        echo "Not logged in";
        exit;
    } else {
        echo json_encode(["success" => false, "message" => "Not logged in"]);
        exit;
    }
}

$config = require __DIR__ . '/config.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

$pet_id = $_POST["pet_id"] ?? '';
$habit_type = $_POST["habit_type"] ?? '';
$habit_description = $_POST["habit_description"] ?? '';
$habit_time = $_POST["habit_time"] ?? '';

if (empty($habit_type) || empty($habit_time)) {
    if ($testing) {
        echo "All fields must be completed";
        exit;
    } else {
        echo json_encode(["success" => false, "message" => "All fields must be completed"]);
        exit;
    }
}

$stmt = $conn->prepare("INSERT INTO pet_habits (pet_id, habit_type, habit_description, habit_time) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $pet_id, $habit_type, $habit_description, $habit_time);

if ($stmt->execute()) {
    if ($testing) {
        echo "success";
        exit;
    } else {
        echo json_encode(["success" => true]);
    }
} else {
    if ($testing) {
        echo "failed";
        exit;
    } else {
        echo json_encode(["success" => false, "message" => $conn->error]);
    }
}
?>
