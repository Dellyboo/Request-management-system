<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "konteldb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$beneficiaire = $_POST['beneficiaire'];
$motif = $_POST['motif'];
$montant = $_POST['montant'];
$facture = $_POST['facture'];

// Handle modes: if no mode is selected, set it as NULL or empty string
$mode = isset($_POST['mode']) ? implode(", ", $_POST['mode']) : NULL; // NULL if no mode selected

// Set default status to "en attente (actuellement au DA&F)"
$status = "en attente (actuellement au DA&F)";

// Insert into the database
$sql = "INSERT INTO paymentgrant (beneficiaire, motif, montant, facture, mode, status) 
        VALUES ('$beneficiaire', '$motif', '$montant', '$facture', '$mode', '$status')";

if ($conn->query($sql) === TRUE) {
    // Redirect to service.php after successful submission
    header("Location:   /G_Request/autorisation/confirmation.php");
    exit();  // Always call exit after header() to stop further script execution
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close connection
$conn->close();
?>
