<?php
// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=konteldb', 'root', '');

// Fetch data from POST request
$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? 0;

$response = ['success' => false, 'message' => ''];

if ($action && $id) {
    if ($action === 'approve') {
        // Update the status of the decaissement
        $stmt = $pdo->prepare("UPDATE decaissement SET status = 'en cours de vérification (actuellement au DA&F)' WHERE id = ?");
        if ($stmt->execute([$id])) {
            $response = ['success' => true, 'message' => 'Demande transmise avec succès.'];
        }
    } elseif ($action === 'delete') {
        // Delete the decaissement
        $stmt = $pdo->prepare("DELETE FROM decaissement WHERE id = ?");
        if ($stmt->execute([$id])) {
            $response = ['success' => true, 'message' => 'Demande refusé avec succès.'];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
