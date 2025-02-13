<?php
include('../database.php'); // Ensure the database connection file path is correct

try {
    $stmt = $pdo->query("SELECT id, position, motif, nom_du_demandeur, poste_du_demandeur, montant_demande, montant_en_lettre, created_at FROM fiche_de_demande_fonds ORDER BY created_at DESC");
    $rows = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}

// Return the fetched data
header('Content-Type: application/json');
echo json_encode($rows);
?>
