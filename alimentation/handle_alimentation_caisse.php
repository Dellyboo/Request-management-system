<?php
// Include database connection
require '../database.php'; // Adjust the path based on your project structure

// Check if the request is valid
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($id > 0 && !empty($action)) {
        try {
            if ($action === 'approve') {
                // Update status for "approve" action
                $query = "UPDATE `alimentation de caisse` SET `status` = 'en cours de vérification (actuellement au DA&F)' WHERE `id` = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$id]);
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'La demande a été transmise avec succès.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Aucune modification effectuée.']);
                }
            } elseif ($action === 'delete') {
                // Delete the record for "delete" action
                $query = "DELETE FROM `alimentation de caisse` WHERE `id` = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$id]);
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'La demande a été refusée avec succès.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Impossible de supprimer la demande.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Action non valide.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur du serveur : ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Données non valides fournies.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requête non autorisée.']);
}
?>
