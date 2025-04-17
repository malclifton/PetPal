<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$config = require 'config.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

$dateNow = date("Y-m-d H:i:s");
$query = "SELECT * FROM schedules WHERE repeat_frequency != 'none' AND scheduled_time <= ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $dateNow);
$stmt->execute();
$result = $stmt->get_result();

while ($task = $result->fetch_assoc()) {
    $newTime = '';
    switch ($task['repeat_frequency']) {
        case 'daily':
            $newTime = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($task['scheduled_time'])));
            break;
        case 'weekly':
            $newTime = date('Y-m-d H:i:s', strtotime('+1 week', strtotime($task['scheduled_time'])));
            break;
        case 'monthly':
            $newTime = date('Y-m-d H:i:s', strtotime('+1 month', strtotime($task['scheduled_time'])));
            break;
    }

    // Insert new task for the next occurrence
    $stmt = $conn->prepare("INSERT INTO schedules (pet_id, owner_id, task, scheduled_time, repeat_frequency) 
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $task['pet_id'], $task['owner_id'], $task['task'], $newTime, $task['repeat_frequency']);
    $stmt->execute();
}
