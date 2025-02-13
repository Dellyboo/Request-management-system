<?php
// Include the database connection
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if (!$id || !in_array($action, ['approve', 'refuse'], true)) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    try {
        if ($action === 'approve') {
            // Update the status to "déjà approuvé"
            $status = 'déjà approuvé';
            $updateQuery = $pdo->prepare("UPDATE transportfee SET status = :status WHERE id = :id");
            $updateQuery->bindParam(':status', $status, PDO::PARAM_STR);
            $updateQuery->bindParam(':id', $id, PDO::PARAM_INT);
            $updateQuery->execute();

            // Redirect back with a success message
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        } elseif ($action === 'refuse') {
            // Delete the record
            $deleteQuery = $pdo->prepare("DELETE FROM transportfee WHERE id = :id");
            $deleteQuery->bindParam(':id', $id, PDO::PARAM_INT);
            $deleteQuery->execute();

            // Redirect back with a success message
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    } catch (PDOException $e) {
        // Redirect back with an error message
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}
