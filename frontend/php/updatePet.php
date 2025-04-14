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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $owner_id = $_SESSION["user_id"];
    $pet_id = $_POST["pet_id"];
    $name = $_POST["name"];
    $species = $_POST["species"];
    $breed = $_POST["breed"];
    $age = (int)$_POST["age"];
    $weight = (float)$_POST["weight"];
    $health_notes = $_POST["health_notes"];

    $favorite_toys = $_POST["favorite_toys"] ?? null;
    $favorite_foods = $_POST["favorite_foods"] ?? null;
    $personality = $_POST["personality"] ?? null;
    $special_needs = $_POST["special_needs"] ?? null;
    $custom_notes = $_POST["custom_notes"] ?? null;

    $image_url = $_POST["current_image"] ?? null;
    if (isset($_FILES["pet-image"]) && $_FILES["pet-image"]["error"] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . "/uploads/";
        $relative_path = "uploads/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_tmp = $_FILES["pet-image"]["tmp_name"];
        $file_name = time() . "_" . basename($_FILES["pet-image"]["name"]);
        $target_path = $upload_dir . $file_name;

        $check = getimagesize($file_tmp);
        if ($check === false) {
            echo json_encode(["success" => false, "message" => "File is not an image."]);
            exit;
        }

        if (move_uploaded_file($file_tmp, $target_path)) {
            $image_url = $relative_path . $file_name;
        } else {
            echo json_encode(["success" => false, "message" => "Failed to upload image"]);
            exit;
        }
    }

    $stmt = $conn->prepare(
        "UPDATE pets SET name = ?, species = ?, breed = ?, age = ?, weight = ?, health_notes = ? WHERE pet_id = ? AND owner_id = ?"
    );
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
        exit;
    }
    $stmt->bind_param("sssssisi", $name, $species, $breed, $age, $weight, $health_notes, $pet_id, $owner_id);

    if ($stmt->execute()) {
        $profile_stmt = $conn->prepare(
            "UPDATE pet_profiles SET image_url = ?, favorite_toys = ?, favorite_foods = ?, personality = ?, special_needs = ?, custom_notes = ? WHERE pet_id = ?"
        );
        if (!$profile_stmt) {
            echo json_encode(["success" => false, "message" => "Profile prepare failed: " . $conn->error]);
            exit;
        }
        $profile_stmt->bind_param("ssssssi", $image_url, $favorite_toys, $favorite_foods, $personality, $special_needs, $custom_notes, $pet_id);

        if ($profile_stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update profile: " . $profile_stmt->error]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update pet: " . $stmt->error]);
    }
}
