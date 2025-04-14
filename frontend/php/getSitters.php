<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$config = require 'config.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed"]);
    exit;
}

$sql = "
    SELECT 
        ps.sitter_id,
        u.name AS name,
        ps.experience_years,
        ps.availability,
        ps.bio,
        ps.profile_image
    FROM pet_sitters ps
    JOIN users u ON ps.sitter_id = u.user_id
";

$result = $conn->query($sql);

$sitters = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $sitters[] = $row;
    }
}

echo json_encode($sitters);
$conn->close();
