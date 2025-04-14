<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

$config = require __DIR__ . '/config.php';

$db_host = $config['db_host'];
$db_user = $config['db_user'];
$db_pass = $config['db_pass'];
$db_name = $config['db_name'];

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$owner_id = $_SESSION["user_id"];

$sql = "
    SELECT 
        p.pet_id, p.name, p.species, p.breed, p.age, p.weight, p.health_notes,
        pp.image_url, pp.favorite_toys, pp.favorite_foods, pp.personality, pp.special_needs, pp.custom_notes
    FROM pets p
    LEFT JOIN pet_profiles pp ON p.pet_id = pp.pet_id
    WHERE p.owner_id = ?
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "Prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();

$pets = $result->fetch_all(MYSQLI_ASSOC);

header("Content-Type: application/json");
echo json_encode($pets);
