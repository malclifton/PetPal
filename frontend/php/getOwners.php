<?php
header("Content-Type: application/json");

session_start();
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$config = require 'config.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

$sql = "SELECT po.owner_id, u.name, u.email, po.profile_image
        FROM pet_owners po
        JOIN users u ON po.owner_id = u.user_id";
$result = $conn->query($sql);

$owners = [];
while ($row = $result->fetch_assoc()) {
    $owners[] = $row;
}

echo json_encode($owners);
$conn->close();
