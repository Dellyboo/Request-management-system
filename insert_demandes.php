<?php
// Database connection
$servername = "localhost"; // Your database server
$username = "root";        // Your database username
$password = "";            // Your database password
$dbname = "konteldb";      // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO demandes (designation, quantite_demandee, observation, status, forwarded_to, user_id, requester_role) VALUES (?, ?, ?, ?, ?, ?, ?)");

$status = "en attente (actuellement au DT&E)"; // Default status
$user_id = 1; // Assuming you have the user ID from session or any logic
$requester_role = $_POST['requester_role']; // Get the role from the dropdown

// Determine forwarded_to based on the selected requester role
$forwarded_to = "";
$redirect_url = ""; // Initialize the redirect URL
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
            $forwarded_to = "Directeur General";
            $redirect_url = "dte.php";
            break;
        default:
            // Handle unexpected values or set a default if necessary
            $redirect_url = "index.php"; // Fallback to home page
            break;
}

foreach ($_POST['designation'] as $key => $designation) {
    $quantite_demandee = $_POST['quantity'][$key];
    $observation = $_POST['observation'][$key];

    // Bind parameters
    $stmt->bind_param("sisssss", $designation, $quantite_demandee, $observation, $status, $forwarded_to, $user_id, $requester_role);

    // Execute the statement
    $stmt->execute();
}

// Close the statement and connection
$stmt->close();
$conn->close();

