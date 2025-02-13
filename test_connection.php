<?php
require 'database.php'; // Ensure this points to your database connection file

try {
    $query = $pdo->query("SELECT 1");
    echo "Database connection successful.";
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}
