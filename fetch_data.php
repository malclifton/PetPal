<?php
$servername = "Local instance MySQL80"; // Replace with your server's name or IP address
$username = "root";       // Your database username
$password = "carti";           // Your database password
$dbname = "petSitterApp"; // The name of your database

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from table
$sql = "SELECT name.pets, owner_id FROM pets"; 
$result = $conn->query($sql);

// Create an array to store the data
$data = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row; // Add each row of data to the array
    }
}

// Return the data as JSON
echo json_encode($data);

// Close the database connection
$conn->close();
?>