<?php
// Database connection
require 'database.php';

header('Content-Type: application/json');

// Check if data is received via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect the data from the POST request
    $id = $_POST['id']; // Retrieve the ID from the request
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $genre = $_POST['genre'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $naissance = $_POST['naissance'];
    $poste = $_POST['poste'];
    $departement = $_POST['departement'];

    try {
        // Ensure the ID is provided
        if (empty($id)) {
            throw new Exception("ID is required.");
        }

        // Update query
        $stmt = $pdo->prepare("
            UPDATE poste 
            SET nom = ?, prenom = ?, genre = ?, email = ?, telephone = ?, naissance = ?, poste = ?, departement = ? 
            WHERE id = ?
        ");
        $stmt->execute([$nom, $prenom, $genre, $email, $telephone, $naissance, $poste, $departement, $id]);

        // Respond with success
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        // Respond with error
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
