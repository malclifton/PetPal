<?php
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}
echo json_encode($user_id);
$config = require __DIR__ . '/config.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
$owner_id = $_GET['owner_id'];
$sitter_id = $_GET['sitter_id'];

$stmt = $conn->prepare("
    SELECT m.message, m.sender_type, o.name AS owner_name, s.name AS sitter_name
    FROM messages m
    LEFT JOIN pet_owners o ON m.sender_type = 'owner' AND m.sender_id = o.owner_id
    LEFT JOIN pet_sitters s ON m.sender_type = 'sitter' AND m.sender_id = s.sitter_id
    WHERE (m.sender_id = ? AND m.receiver_id = ? AND m.sender_type = 'owner')
       OR (m.sender_id = ? AND m.receiver_id = ? AND m.sender_type = 'sitter')
    ORDER BY m.id ASC
");

$stmt->bind_param("iiii", $owner_id, $sitter_id, $sitter_id, $owner_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $name = $row['sender_type'] === 'owner' ? $row['owner_name'] : $row['sitter_name'];
    $messages[] = [
        "message" => $row['message'],
        "sender" => $name
    ];
}

echo json_encode($messages);
