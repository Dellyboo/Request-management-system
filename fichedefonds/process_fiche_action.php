<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "konteldb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the request is a POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get the action and ID from the POST data
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // Validate the action and ID
    if ($id > 0 && in_array($action, ['approve', 'delete'])) {
        if ($action === 'approve') {
            // Update the status to "en cours de verification"
            $sql = "UPDATE fiche_de_demande_fonds SET status = 'en cours de vérification (actuellement au DG)' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo "Status updated successfully for ID $id.";
            } else {
                echo "Error updating status: " . $conn->error;
            }
            $stmt->close();
        } elseif ($action === 'delete') {
            // Delete the entry from the database
            $sql = "DELETE FROM fiche_de_demande_fonds WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo "Entry deleted successfully for ID $id.";
            } else {
                echo "Error deleting entry: " . $conn->error;
            }
            $stmt->close();
        }
    } else {
        echo "Invalid ID or action.";
    }
} else {
    echo "Invalid request method.";
}

// Close the database connection
$conn->close();
?>
