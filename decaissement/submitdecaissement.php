<?php
// Include the database connection file
include '../database.php'; // Adjust the path to your database file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Prepare the SQL query to insert data into the database
        $stmt = $pdo->prepare("
            INSERT INTO decaissement (
                motif_de_decaissement, 
                nom_prenom_beneficiaire, 
                montant, 
                total, 
                total_en_lettres, 
                date, 
                nom_prenom_demandeur, 
                poste_demandeur, 
                forwarded_to, 
                status
            ) VALUES (
                :motif_de_decaissement, 
                :nom_prenom_beneficiaire, 
                :montant, 
                :total, 
                :total_en_lettres, 
                :date, 
                :nom_prenom_demandeur, 
                :poste_demandeur, 
                :forwarded_to, 
                :status
            )
        ");

        // Check if "Total en lettres" is provided
        if (!isset($_POST['total_in_words']) || empty(trim($_POST['total_in_words']))) {
            throw new Exception("Total en lettres is required.");
        }
        
        // Get the manually entered "Total en lettres"
        $total_en_lettres = trim($_POST['total_in_words']);
        
        // Sum up the total and prepare data for insertion
        $total = 0;
        $rows = count($_POST['designation']);
        
        for ($i = 0; $i < $rows; $i++) {
            // Check if nom_et_prenom is set and not empty
            if (!isset($_POST['nom_et_prenom'][$i]) || empty($_POST['nom_et_prenom'][$i])) {
                throw new Exception("Nom et prénom du bénéficiaire is required for row $i.");
            }
            $total += $_POST['montant'][$i];
        }

        // Loop through each row and execute the query
for ($i = 0; $i < $rows; $i++) {
    $stmt->execute([
        ':motif_de_decaissement' => $_POST['designation'][$i],
        ':nom_prenom_beneficiaire' => $_POST['nom_et_prenom'][$i],
        ':montant' => $_POST['montant'][$i],
        ':total' => $total,
        ':total_en_lettres' => $total_en_lettres,
        ':date' => date('Y-m-d'),
        ':nom_prenom_demandeur' => 'Ntirandekura Sandrine',
        ':poste_demandeur' => 'Caissière',
        ':forwarded_to' => 'Bizimana Youssouf',
        ':status' => 'en attente (actuellement au Chef Comptable)',
    ]);
}


        // Check if redirect_url is set in the query string, otherwise, use a default page
        $redirect_url = isset($_GET['redirect_url']) ? $_GET['redirect_url'] : '/caissiere.php';
        
        // Define the base path for your project
        $base_path = ''; // Adjust the path as needed
        $redirect_url = $base_path . ltrim($redirect_url, '/'); // Ensure no extra slashes

        // Redirect to the confirmation page with the redirect_url parameter
        header("Location: confirmation1.php?redirect_url=" . urlencode($redirect_url));
        exit();

    } catch (PDOException $e) {
        // Handle database connection or SQL errors
        echo "Error: " . $e->getMessage();
    } catch (Exception $e) {
        // Handle form validation or other general errors
        echo "Error: " . $e->getMessage();
    }
}
?>
