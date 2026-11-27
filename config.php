<?php

$conn = new mysqli("localhost", "root", "", "resturant-db");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

?>