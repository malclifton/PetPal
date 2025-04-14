<?php
session_start();
$_SESSION["role"] = $role;
$config = require __DIR__ . '/config.php';

$db_host = $config['db_host'];
$db_user = $config['db_user'];
$db_pass = $config['db_pass'];
$db_name = $config['db_name'];

// Form data
$fullName = trim($_POST["fullName"]);
$email = trim($_POST["email"]);
$password = $_POST["password"];
$phoneNumber = trim($_POST["phoneNumber"]);
$role = strtolower(trim($_POST["role"]));

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

// Connect to database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
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
    $user_id = $stmt->insert_id;
    $stmt->close();

    // Insert into role-specific table
    if ($role === "owner") {
        $ownerAddress = trim($_POST["ownerAddress"]);
        if (empty($ownerAddress)) {
            die("Address is required for pet owners.");
        }
        $roleStmt = $conn->prepare("INSERT INTO pet_owners (owner_id) VALUES (?)");
    } elseif ($role === "sitter") {
        $roleStmt = $conn->prepare("INSERT INTO pet_sitters (sitter_id) VALUES (?)");
    } else {
        die("Invalid role selected.");
    }

    $roleStmt->bind_param("i", $user_id);

    if ($role === "owner") {
        $ownerAddress = trim($_POST["ownerAddress"]);
        if (empty($ownerAddress)) {
            die("Address is required for pet owners.");
        }
        $target_dir = "uploads/";
        $ownerProfileImage = null;

        if (!empty($_FILES["ownerProfileImage"]["name"])) {
            $ownerProfileImage = $target_dir . basename($_FILES["ownerProfileImage"]["name"]);
            move_uploaded_file($_FILES["ownerProfileImage"]["tmp_name"], $ownerProfileImage);
        }
        $roleStmt = $conn->prepare("INSERT INTO pet_owners (owner_id, address, profile_image) VALUES (?, ?, ?)");
        $roleStmt->bind_param("iss", $user_id, $ownerAddress, $ownerProfileImage);
    } elseif ($role === "sitter") {
        $experienceYears = intval($_POST["experienceYears"]);
        $availability = trim($_POST["availability"]);
        $bio = trim($_POST["bio"]);

        if (empty($availability)) {
            die("Availability is required for pet sitters.");
        }

        $imagePath = null;
        if (isset($_FILES["profileImage"]) && $_FILES["profileImage"]["error"] === UPLOAD_ERR_OK) {
            $imageTmpPath = $_FILES["profileImage"]["tmp_name"];
            $imageName = basename($_FILES["profileImage"]["name"]);
            $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION);
            $uniqueName = uniqid("sitter_") . "." . $imageExtension;

            $uploadDir = "./uploads/sitters/";
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $imagePath = $uploadDir . $uniqueName;
            move_uploaded_file($imageTmpPath, $imagePath);
        }
        $roleStmt = $conn->prepare("INSERT INTO pet_sitters (sitter_id, experience_years, availability, bio, profile_image VALUES (?, ?, ?, ?, ?)");
        $roleStmt->bind_param("iisss", $user_id, $experienceYears, $availability, $bio, $imagePath);
    }


    if ($roleStmt->execute()) {
        $roleStmt->close();
        $conn->close();
        header("Location: ./signIn.html");
        exit;
    } else {
        die("Error adding to role table: " . $roleStmt->error);
    }
} else {
    if ($conn->errno == 1062) {
        die("Error: This email is already registered. Try a different email.");
    } else {
        die("Error: " . $stmt->error);
    }
}
