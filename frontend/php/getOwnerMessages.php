<?php
session_start();
$sender_id = $_SESSION['user_id'];
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$config = require __DIR__ . '/config.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

$owner1_id = $_GET['owner1_id'] ?? null;
$owner2_id = $_GET['owner2_id'] ?? null;

if (!$owner1_id || !$owner2_id) {
    echo json_encode(["success" => false, "message" => "Missing parameters"]);
    exit;
}

$sql = "
    SELECT 
        om.id,
        om.sender_id,
        sender.name AS sender_name,
        om.receiver_id,
        receiver.name AS receiver_name,
        om.message,
        om.timestamp
    FROM owner_messages om
    JOIN users sender ON om.sender_id = sender.user_id
    JOIN users receiver ON om.receiver_id = receiver.user_id
    WHERE 
        (om.sender_id = ? AND om.receiver_id = ?)
        OR (om.sender_id = ? AND om.receiver_id = ?)
    ORDER BY om.timestamp ASC
";


$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "SQL error: " . $conn->error]);
    exit;
}

$stmt->bind_param("iiii", $owner1_id, $owner2_id, $owner2_id, $owner1_id);
$stmt->execute();

$result = $stmt->get_result();
$messages = [];

while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'sender' => $row['sender_name'],
        'receiver' => $row['receiver_name'],
        'message' => $row['message'],
        'timestamp' => $row['timestamp']
    ];
}

echo json_encode($messages);
