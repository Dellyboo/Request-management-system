<?php
// Include the database connection
require '../database.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the request data
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // Handle the 'approve' action
    if ($action === 'approve') {
        // Update the status to 'déjà approuvé'
        $query = $pdo->prepare("UPDATE transportfee SET status = 'déjà approuvé' WHERE id = :id");
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($query->execute()) {
            header("Location: index.php?status=approved"); // Redirect to the same page
            exit;
        } else {
            echo "Error: Failed to approve the request.";
        }
    }

    // Handle the 'refuse' action (delete request)
    if ($action === 'refuse') {
        // Delete the request from the database
        $query = $pdo->prepare("DELETE FROM transportfee WHERE id = :id");
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($query->execute()) {
            header("Location: index.php?status=refused"); // Redirect to the same page
            exit;
        } else {
            echo "Error: Failed to delete the request.";
        }
    }
}
?>
