<?php
session_start();
require 'database.php';

$response = ['status' => 'error'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'];

        if ($action === 'edit_profile') {
            // Update profile details for id = 2 in the 'poste' table
            $stmt = $pdo->prepare("
                UPDATE poste 
                SET Nom = ?, Prenom = ?, Genre = ?, email = ?, telephone = ?, naissance = ?, poste = ?, Departement = ? 
                WHERE id = 2
            ");
            $stmt->execute(['NewNom', 'NewPrenom', 'NewGenre', 'newemail@example.com', '123456789', '1990-01-01', 'NewPoste', 'NewDepartement']);

            $response['status'] = 'success';
        } elseif ($action === 'change_password') {
            // Update password and related details for id = 2 in the 'poste' table
            $stmt = $pdo->prepare("
                UPDATE poste 
                SET email = ?, password = ?, language_preference = ?, role = ? 
                WHERE id = 2
            ");
            $stmt->execute(['newemail@example.com', 'newpassword123', 'French', 'UserRole']);

            $response['status'] = 'success';
        }
    }
} catch (PDOException $e) {
    $response['message'] = 'Erreur: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
