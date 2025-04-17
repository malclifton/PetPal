<?php
header("Content-Type: application/json");

session_start();
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$config = require __DIR__ . '/config.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
$data = json_decode(file_get_contents("php://input"), true);

$sender_id = $_SESSION['user_id'];
$receiver_id = $data['receiver_id'];
$message = $data['message'];
$sender_type = $data['sender_type'];

$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, sender_type) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $sender_id, $receiver_id, $message, $sender_type);
$stmt->execute();
$stmt->close();

echo json_encode(["status" => "success"]);
