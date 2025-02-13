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

// Prepare the SQL query with all required columns
$stmt = $conn->prepare("INSERT INTO demandes (designation, quantite_demandee, observation, status, forwarded_to, requester_role, date_demande, nom_et_prenom) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

$status = "en cours de vérification (actuellement au DG)";
$user_id = 1;
$requester_role = $_POST['requester_role'];
$nom_et_prenom = $_POST['nom_et_prenom']; // Ensure this field is passed in the POST request

// Determine forwarded_to and redirect URL based on requester role
$forwarded_to = "";
$redirect_url = "";
switch ($requester_role) {
    case "Chef de Projet":
        $forwarded_to = "Directeur Technique et Exploitation";
        $redirect_url = "projet.php";
        break;
    case "Chef de Service":
        $forwarded_to = "Directeur administratif et financier";
        $redirect_url = "service.php";
        break;
    case "DAF":
        $forwarded_to = "Directeur General";
        $redirect_url = "daf.php";
        break;
    case "DTE":
        $forwarded_to = "Directeur administratif et financier";
        $redirect_url = "dte.php";
        break;
    default:
        $redirect_url = "default.php";
        break;
}

foreach ($_POST['designation'] as $key => $designation) {
    $quantite_demandee = $_POST['quantity'][$key];
    $observation = $_POST['observation'][$key];
    $date_demande = date("Y-m-d H:i:s"); // Current timestamp

    // Bind parameters and execute the statement
    $stmt->bind_param("sissssss", $designation, $quantite_demandee, $observation, $status, $forwarded_to, $requester_role, $date_demande, $nom_et_prenom);
    $stmt->execute();
}

// Close the statement and connection
$stmt->close();
$conn->close();

// Redirect to the success page with the target URL
header("Location: success.php?redirect_url=$redirect_url");
exit();
?>
