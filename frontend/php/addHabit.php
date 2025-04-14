<?php
header("Content-Type: application/json");

session_start();
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$config = require 'config.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

$pet_id = $_POST["pet_id"];
$habit_type = $_POST["habit_type"];
$habit_description = $_POST["habit_description"];
$habit_time = $_POST["habit_time"];

$stmt = $conn->prepare("INSERT INTO pet_habits (pet_id, habit_type, habit_description, habit_time) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $pet_id, $habit_type, $habit_description, $habit_time);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => $conn->error]);
}
