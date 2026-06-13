<?php

$db_name = 'mysql:host=localhost;port=3307;dbname=food_db';
$user_name = 'root';
$user_password = '';

try {
    $conn = new PDO($db_name, $user_name, $user_password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // If connection fails, display an error message
    echo "Connection failed: " . $e->getMessage();
}

?>