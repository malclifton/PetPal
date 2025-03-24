<?php
// Start the session
session_start();
$config = require __DIR__ . '/config.php';

$db_host = $config['db_host'];
$db_user = $config['db_user'];
$db_pass = $config['db_pass'];
$db_name = $config['db_name'];

// Connect to database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    echo "Could not connect to server\n";
    die("connection failed: " . $conn->connect_error);
} else {
    echo "Connection established\n";
}
echo mysqli_get_server_info($conn) . "\n";

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Validate inputs
    if (empty($email)) {
        die("Email address is required.");
    }
    if (empty($password)) {
        die("Password is required.");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Fetch user from the database
    $stmt = $conn->prepare("SELECT user_id, name, email, password_hash FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user["password_hash"])) {
            // Password is correct --> start a session
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_email"] = $user["email"];

            // Go to dashboard 
            header("Location: PUT DASHBOARD HERE ");
            exit;
        } else {
            // Invalid password
            die("Invalid email or password.");
        }
    } else {
        // User not found
        die("Invalid email or password.");
    }

    $stmt->close();
}

$conn->close();
?>