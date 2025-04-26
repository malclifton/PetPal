<?php
session_start();

$config = require __DIR__ . '/config.php';
$conn = new mysqli(
    $config['db_host'],
    $config['db_user'],
    $config['db_pass'],
    $config['db_name']
);

if ($conn->connect_error) {
    echo "Database connection failed";
    exit;
}

// Detect if Testing Mode is ON
$testing = isset($_GET['testing']) && $_GET['testing'] === 'true';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';

    // Validate inputs
    if (empty($email) || empty($password)) {
        if ($testing) {
            echo "All fields are required";
            exit;
        } else {
            die("All fields are required");
        }
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if ($testing) {
            echo "Invalid email format";
            exit;
        } else {
            die("Invalid email format");
        }
    }

    // Fetch user
    $stmt = $conn->prepare("SELECT user_id, name, email, password_hash, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user["password_hash"])) {
            // Correct password
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_email"] = $user["email"];
            $_SESSION["role"] = $user["role"];

            if ($testing) {
                echo "Login successful";
                exit;
            } else {
                if ($user["role"] === "owner") {
                    header("Location: ../petOwnerDashboard.html");
                    exit;
                } elseif ($user["role"] === "sitter") {
                    header("Location: ../petSitterDashboard.html");
                    exit;
                }
            }
        } else {
            // Invalid password
            if ($testing) {
                echo "Invalid email or password";
                exit;
            } else {
                die("Invalid email or password");
            }
        }
    } else {
        // User not found
        if ($testing) {
            echo "Invalid email or password";
            exit;
        } else {
            die("Invalid email or password");
        }
    }
}

$conn->close();
?>
