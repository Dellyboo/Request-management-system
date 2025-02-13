<?php
session_start();
require 'database.php';

// Fetch data from the demandes table
$query = "SELECT designation, status, COUNT(*) as count FROM demandes GROUP BY designation, status";
$stmt = $pdo->prepare($query);
$stmt->execute();

// Fetch all results
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize arrays to store counts for each status
$totals = [];
$approuves = [];
$verification = [];
$enattentes = [];

foreach ($results as $row) {
    if ($row['status'] === 'déjà approuvé') {
        $approuves[$row['designation']] = $row['count'];
    } elseif ($row['status'] === 'en cours de vérification') {
        $verification[$row['designation']] = $row['count'];
    } elseif ($row['status'] === 'en attente') {
        $enattentes[$row['designation']] = $row['count'];
    }
    $totals[$row['designation']] = $row['count'];
}

// Return the data as a JSON response
echo json_encode([
    'totals' => $totals,
    'approuves' => $approuves,
    'verification' => $verification,
    'enattentes' => $enattentes
]);
?>
