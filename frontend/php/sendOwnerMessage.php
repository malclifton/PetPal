<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$config = require __DIR__ . '/config.php';

$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

$data = json_decode(file_get_contents("php://input"), true);

$sender_id = $_SESSION["user_id"];
$receiver_id = $_POST["receiver_id"];
$message = $_POST["message"];

$sql = "INSERT INTO owner_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $sender_id, $receiver_id, $message);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}
