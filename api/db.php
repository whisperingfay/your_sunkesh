<?php
$host = 'localhost';
$db   = 'senkesh';
$user = 'root';
$pass = '';
$port = 3307; // Use your port

// Include charset in mysqli constructor
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Set charset to utf8
if (!$conn->set_charset('utf8')) {
    die('Error loading character set utf8: ' . $conn->error);
}
?>