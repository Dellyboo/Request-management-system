<?php
include('../database.php'); // Ensure this path is correct

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Prepare the SQL statement
        $stmt = $pdo->prepare("
            INSERT INTO fiche_de_demande_fonds (
                nom_du_demandeur, 
                poste_du_demandeur, 
                motif, 
                montant_demande, 
                montant_en_lettre, 
                created_at
            ) 
            VALUES (
                :nom_du_demandeur, 
                :poste_du_demandeur, 
                :motif, 
                :montant_demande, 
                :montant_en_lettre, 
                :created_at
            )
        ");

        // Bind form inputs to table columns
        $stmt->bindParam(':nom_du_demandeur', $_POST['nom_demandeur']);
        $stmt->bindParam(':poste_du_demandeur', $_POST['poste_demandeur']);
        $stmt->bindParam(':motif', $_POST['motif']);
        $stmt->bindParam(':montant_demande', $_POST['montant_chiffre']);
        $stmt->bindParam(':montant_en_lettre', $_POST['montant_lettre']);
        
        // Set the created_at timestamp
        $created_at = date('Y-m-d H:i:s');
        $stmt->bindParam(':created_at', $created_at);

        // Execute the statement
        $stmt->execute();

        // Define the redirect page based on the user's position
        $poste = $_POST['poste_demandeur'];

        switch ($poste) {
            case "Directeur Administratif et Financier":
                $redirectPage = "../daf.php";
                break;
            case "Directeur technique et exploitation":
                $redirectPage = "../dte.php";
                break;
            case "Chef Comptable":
                $redirectPage = "../service.php";
                break;
            case "Chef de projet":
                $redirectPage = "../projet.php";
                break;
            default:
                $redirectPage = "../default.php";
        }

        // Store the redirect page in a session or pass via URL
        session_start();
        $_SESSION['redirectPage'] = $redirectPage;

        // Redirect to the confirmation page
        header("Location: confirmation.php");
        exit;

    } catch (PDOException $e) {
        // Error handling
        echo "Error: " . $e->getMessage();
    }
}
?>
