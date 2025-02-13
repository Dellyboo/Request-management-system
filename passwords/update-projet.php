<?php
require '../database.php';

// Ensure the response is in JSON format
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect the data from the POST request
    $password = $_POST['password']; // Plain text password

    // User ID to update
    $userId = 3; // Specific user ID

    // Initialize response array
    $response = array();

    try {
        // Prepare and execute the update query (update only the password for user ID 3)
        $stmt = $pdo->prepare("UPDATE poste SET password = ? WHERE id = ?");
        $stmt->execute([$password, $userId]);

        // If the update is successful, return a success response
        $response['status'] = 'success';
        $response['message'] = 'Password updated successfully.';
        echo json_encode($response);
    } catch (Exception $e) {
        // If something goes wrong, return an error response
        $response['status'] = 'error';
        $response['message'] = $e->getMessage();
        echo json_encode($response);
    }
}
?>
