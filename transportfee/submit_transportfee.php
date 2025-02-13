<?php
// Include database connection
include '../database.php'; // Adjusted path

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form data
    $nom_et_prenom = htmlspecialchars($_POST['nom']);
    $poste = htmlspecialchars($_POST['poste']);
    $telephone = filter_var($_POST['telephone'], FILTER_SANITIZE_NUMBER_INT); // Sanitize as number
    $lieu_depart = htmlspecialchars($_POST['lieu_depart']);
    $adresse_destination = htmlspecialchars($_POST['adresse_destination']);
    $motif_deplacement = htmlspecialchars($_POST['motif_deplacement']);
    $aller = htmlspecialchars($_POST['aller']);
    $aller_retour = htmlspecialchars($_POST['aller_retour']);
    $taxi = htmlspecialchars($_POST['taxi']);
    $transport_commun = htmlspecialchars($_POST['transport_commun']);
    $location_voiture = htmlspecialchars($_POST['location_voiture']);
    $montant = filter_var($_POST['montant'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION); // Sanitize amount as float
    $forwarded_to = null;

    // Determine the status value based on the poste
    switch ($poste) {
        case "Chef de Projet":
        case "Informaticien":
        case "Maintenancier des scanners":
        case "Suivi électronique":
            $status = "en attente (actuellement au DT&E)";
            break;
        case "Directeur administratif et financier":
            $status = "en cours de vérification (actuellement au DG)";
            break;
        default:
            $status = "en attente (actuellement au DA&F)"; // Default status
    }

    // Determine the forwarded_to value based on the poste
    switch ($poste) {
        case "Caissière":
        case "Chef Comptable":
        case "Comptable":
        case "Technicien de surface":
            $forwarded_to = "Directeur administratif et financier";
            break;
        case "Chef de Projet":
        case "Informaticien":
        case "Maintenancier des scanners":
        case "Suivi électronique":
            $forwarded_to = "Directeur technique et exploitation";
            break;
        case "Directeur administratif et financier":
        case "Directeur technique et exploitation":
            $forwarded_to = "Directeur général";
            break;
    }

    // Basic validation
    if (!is_numeric($telephone) || strlen($telephone) < 8) {
        echo "Invalid phone number.";
        exit();
    }

    if (!is_numeric($montant) || $montant <= 0) {
        echo "Invalid amount.";
        exit();
    }

    // Prepare SQL statement with placeholders for data
    $sql = "INSERT INTO transportfee (nom_et_prenom, poste, telephone, lieu_depart, adresse_destination, motif_deplacement, aller, aller_retour, taxi, transport_commun, location_voiture, montant, status, forwarded_to, created_at) 
            VALUES (:nom_et_prenom, :poste, :telephone, :lieu_depart, :adresse_destination, :motif_deplacement, :aller, :aller_retour, :taxi, :transport_commun, :location_voiture, :montant, :status, :forwarded_to, CURDATE())";

    // Prepare the statement
    $stmt = $pdo->prepare($sql);

    // Bind parameters to the placeholders in the SQL statement
    $stmt->bindParam(':nom_et_prenom', $nom_et_prenom, PDO::PARAM_STR);
    $stmt->bindParam(':poste', $poste, PDO::PARAM_STR);
    $stmt->bindParam(':telephone', $telephone, PDO::PARAM_STR);
    $stmt->bindParam(':lieu_depart', $lieu_depart, PDO::PARAM_STR);
    $stmt->bindParam(':adresse_destination', $adresse_destination, PDO::PARAM_STR);
    $stmt->bindParam(':motif_deplacement', $motif_deplacement, PDO::PARAM_STR);
    $stmt->bindParam(':aller', $aller, PDO::PARAM_STR);
    $stmt->bindParam(':aller_retour', $aller_retour, PDO::PARAM_STR);
    $stmt->bindParam(':taxi', $taxi, PDO::PARAM_STR);
    $stmt->bindParam(':transport_commun', $transport_commun, PDO::PARAM_STR);
    $stmt->bindParam(':location_voiture', $location_voiture, PDO::PARAM_STR);
    $stmt->bindParam(':montant', $montant, PDO::PARAM_STR);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':forwarded_to', $forwarded_to, PDO::PARAM_STR);

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect based on the poste value
        switch ($poste) {
            case "Directeur général":
                $redirectPage = "index.php";
                break;
            case "Directeur administratif et financier":
                $redirectPage = "daf.php";
                break;
            case "Directeur technique et exploitation":
                $redirectPage = "dte.php";
                break;
            case "Chef Comptable":
                $redirectPage = "service.php";
                break;
            case "Chef de Projet":
                $redirectPage = "projet.php";
                break;
            default:
                $redirectPage = "default.php";
        }

        // Redirect to the confirmation page with the redirect URL
        header("Location: confirmation.php?redirect_url=$redirectPage");
        exit(); // Always call exit after header redirection
    } else {
        // Handle the error appropriately
        echo "An error occurred. Please try again later.";
        error_log("Error: " . $stmt->errorInfo()[2]); // Log the error
    }

    // Close the statement (optional with PDO, not strictly necessary)
    $stmt->closeCursor();
}
?>