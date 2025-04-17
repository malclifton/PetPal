<?php
$config = require __DIR__ . '/config.php';

$db_host = $config['db_host'];
$db_user = $config['db_user'];
$db_pass = $config['db_pass'];
$db_name = $config['db_name'];

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Database connection failed"]));
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['userId'], $data['petId'], $data['message'], $data['sendTime'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing fields"]);
    exit;
}

$status = $data['status'] ?? 'unread';

$stmt = $conn->prepare("INSERT INTO pet_notifications (user_id, pet_id, message, send_time, status) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisss", $data['userId'], $data['petId'], $data['message'], $data['sendTime'], $status);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "id" => $stmt->insert_id]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Insert failed"]);
}
?>
