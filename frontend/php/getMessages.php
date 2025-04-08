<?php
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$config = require 'config.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
$owner_id = $_GET['owner_id'];
$sitter_id = $_GET['sitter_id'];

$sql = "SELECT * FROM messages 
        WHERE (sender_id = ? AND receiver_id = ? AND sender_type = 'owner') 
           OR (sender_id = ? AND receiver_id = ? AND sender_type = 'sitter')
        ORDER BY timestamp ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $owner_id, $sitter_id, $sitter_id, $owner_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
echo json_encode($messages);
