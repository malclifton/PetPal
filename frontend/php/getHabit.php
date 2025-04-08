<?php
header("Content-Type: application/json");
session_start();

if (!isset($_SESSION["user_id"])) {
    echo json_encode([]);
    exit;
}

$config = require 'config.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

if (isset($_GET["habit_id"])) {
    // Return single habit for editing
    $habit_id = $_GET["habit_id"];
    $stmt = $conn->prepare("SELECT * FROM pet_habits WHERE habit_id = ?");
    $stmt->bind_param("i", $habit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $habit = $result->fetch_assoc();
    echo json_encode($habit ?: []);
} elseif (isset($_GET["pet_id"])) {
    // Return all habits for pet
    $pet_id = $_GET["pet_id"];
    $stmt = $conn->prepare("SELECT * FROM pet_habits WHERE pet_id = ? ORDER BY habit_time DESC");
    $stmt->bind_param("i", $pet_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $habits = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($habits);
} else {
    echo json_encode([]);
}
