<?php 

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if the AJAX request is being made
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && isset($_POST['id'])) {
    $action = $_POST['action'];
    $id = intval($_POST['id']);  // Ensure the ID is treated as an integer

    // Database connection using PDO
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "konteldb";

    try {
        // Establish the PDO connection
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($action === "approve") {
            // Update the status to "en cours de vérification" when approved
            $sql = "UPDATE transportfee SET status = 'en cours de vérification (actuellement au DG)' WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        } elseif ($action === "delete") {
            // Delete the row if the user clicks 'Refuser'
            $sql = "DELETE FROM transportfee WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        }

        // Return a success message
        echo "Action completed successfully!";
    } catch (PDOException $e) {
        // If there’s an error, display the error message
        echo "Error: " . $e->getMessage();
    }

    // Close the database connection
    $conn = null;

    exit();  // End the script
}
?>
