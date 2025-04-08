<?php
header("Content-Type: application/json");

session_start();
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$config = require 'config.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
$data = json_decode(file_get_contents("php://input"), true);

$sender_id = $data['sender_id'];
$receiver_id = $data['receiver_id'];
$sender_type = $data['sender_type'];
$message = $data['message'];

$sql = "INSERT INTO messages (sender_id, receiver_id, sender_type, message) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiss", $sender_id, $receiver_id, $sender_type, $message);
$stmt->execute();

echo json_encode(["status" => "success"]);
