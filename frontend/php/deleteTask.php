<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit;
}


$config = require __DIR__ . '/config.php';

$db_host = $config['db_host'];
$db_user = $config['db_user'];
$db_pass = $config['db_pass'];
$db_name = $config['db_name'];

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}


$id = $_POST['id'];

$stmt = $conn->prepare("DELETE FROM schedules WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
echo "Task deleted!";
