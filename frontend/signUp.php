<?php

// Form data
$fullName = trim($_POST["fullName"]);
$email = trim($_POST["email"]);
$password = $_POST["password"];
$phoneNumber = trim($_POST["phoneNumber"]);
$role = isset($_POST["owner"]) ? "Owner" : (isset($_POST["sitter"]) ? "Sitter" : "User");

// Password Hash
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Validate inputs
if (empty($fullName)) {
    die("Full name required");
}
if (empty($email)) {
    die("Email address required");
}
if (empty($password)) {
    die("Password required");
}
if (empty($phoneNumber)) {
    die("Phone number required");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Valid email address required");
}
if (strlen($password) < 6) {
    die("Password must be at least 6 characters");
}
if (!preg_match("/[a-zA-Z]/", $password)) {
    die("Password must contain at least one letter");
}
if (!preg_match("/\d/", $password)) {
    die("Password must contain at least one number");
}
if (!preg_match("/^\d+$/", $phoneNumber)) {
    die("Phone number can only contain digits.");
}

// Database credentials
$host = "localhost";
$user = "";
$pass = "";
$dbname = "";

// Connect to database
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo "Could not connect to server\n";
    die("connection failed: " . $conn->connect_error);
} else {
    echo "Connection established\n";
}
echo mysqli_get_server_info($conn) . "\n";

// Prepare SQL statement 
$stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, phone, role) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $fullName, $email, $password_hash, $phoneNumber, $role);

// Execute
if ($stmt->execute()) {
    header("Location: ./signIn.html");  // Redirect to login page
    $stmt->close();
    $conn->close();
    exit;
} else {
    if ($conn->errno == 1062) {
        die("Error: This email already registered. Try a different email.");
    } else {
        die("Error: " . $stmt->error);
    }
}
