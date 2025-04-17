<?php
header('Content-Type: application/json');
session_start();
$config = require __DIR__ . '/config.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

if (!empty($_FILES['newProfileImage']['name'])) {
    $upload_dir = realpath(__DIR__ . '../uploads/');
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $filename = basename($_FILES['newProfileImage']['name']);
    $file = $target_dir . uniqid() . "_" . $filename;

    if (move_uploaded_file($_FILES['newProfileImage']['tmp_name'], $file)) {
        $stmt = $conn->prepare("UPDATE pet_owners SET profile_image = ? WHERE owner_id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $file, $userId);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'new_image' => $file]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Database update failed']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Statement preparation failed']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
}
