<?php
// Include database connection
require 'database.php';

// Function to create and forward requests
function createAndForwardRequest($userId, $item, $requesterRole) {
    global $db; // Use the global database connection

    // Set default status
    $status = "en attente";
    $forwardedTo = "";

    // Determine who to forward the request to based on the requester's role
    switch ($requesterRole) {
        case "Chef de service":
            $forwardedTo = "DAF";
            break;
        case "Chef de projet":
            $forwardedTo = "DTE";
            break;
        case "DAF":
        case "DTE":
            $forwardedTo = "Directeur Generel";
            break;
        default:
            throw new Exception("Invalid role");
    }

    // Insert the request into the database
    $stmt = $db->prepare("INSERT INTO demandes (user_id, item, requester_role, status, forwarded_to) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $item, $requesterRole, $status, $forwardedTo]);
}

// Example usage (make sure to call this function when appropriate)
// createAndForwardRequest($userId, $item, $requesterRole);
?>
