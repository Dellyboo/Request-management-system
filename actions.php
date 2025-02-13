<?php
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $id = (int)$_POST['id'];

    // Define the allowed statuses
    $validStatuses = [
        'en cours de vérification (actuellement au DG)',
        'en attente (actuellement au DA&F)',
        'en cours de vérification (actuellement au DA&F)',
        'déjà approuvé',
        'en attente (actuellement au DT&E)'
    ];

    // Convert statuses to a parameterized string for SQL
    $placeholders = implode(',', array_fill(0, count($validStatuses), '?'));

    if ($action === 'approve') {
        // Approve action: Update status in database
        $stmt = $pdo->prepare("UPDATE demandes SET status = 'déjà approuvé' WHERE id = ? AND status IN ($placeholders)");
        $params = array_merge([$id], $validStatuses); // Merge ID with statuses
        $success = $stmt->execute($params);

        echo json_encode(['success' => $success]);
    } elseif ($action === 'delete') {
        // Delete action: Remove entry from database
        $stmt = $pdo->prepare("DELETE FROM demandes WHERE id = ? AND status IN ($placeholders)");
        $params = array_merge([$id], $validStatuses); // Merge ID with statuses
        $success = $stmt->execute($params);

        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
