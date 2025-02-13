<?php
require '../database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $action = $_POST['action'];

    if ($action === 'approve') {
        // Update the status to 'déjà approuvé'
        $query = $pdo->prepare("UPDATE fiche_de_demande_fonds SET status = 'déjà approuvé' WHERE id = :id");
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        if ($query->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Demande approuvée avec succès.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'approbation de la demande.']);
        }
    } elseif ($action === 'refuse') {
        // Delete the row from the table
        $query = $pdo->prepare("DELETE FROM fiche_de_demande_fonds WHERE id = :id");
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        if ($query->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Demande refusée avec succès.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors du refus de la demande.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Action non valide.']);
    }
}
?>
