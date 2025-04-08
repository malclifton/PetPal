<?php
header("Content-Type: application/json");

session_start();
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$config = require 'config.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

$sql = "SELECT sitter_id, name, experience_years, availability, bio, profile_image_url FROM pet_sitters";
$result = $conn->query($sql);

$sitters = [];
while ($row = $result->fetch_assoc()) {
    $sitters[] = $row;
}

header('Content-Type: application/json');
echo json_encode($sitters);

$conn->close();
