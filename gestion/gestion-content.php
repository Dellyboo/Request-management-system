<?php
include '../database.php'; // Include the database connection file

// Fetch employees from the `poste` table
$employees = $pdo->query("SELECT * FROM poste")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_employee'])) {
    // Insert new employee data
    $stmt = $pdo->prepare("
        INSERT INTO poste (Nom, Prenom, Genre, email, telephone, naissance, poste, Departement, password, `Nom et Prenom`) 
        VALUES (:Nom, :Prenom, :Genre, :email, :telephone, :naissance, :poste, :Departement, :password, :nom_prenom)
    ");
    $stmt->execute([
        ':Nom' => $_POST['Nom'],
        ':Prenom' => $_POST['Prenom'],
        ':Genre' => $_POST['Genre'],
        ':email' => $_POST['email'],
        ':telephone' => $_POST['telephone'],
        ':naissance' => $_POST['naissance'],
        ':poste' => $_POST['poste'],
        ':Departement' => $_POST['Departement'],
        ':password' => '1111', // Default password
        ':nom_prenom' => $_POST['Nom'] . ' ' . $_POST['Prenom'],
    ]);

    header("Location: gestion-content.php"); // Redirect to refresh the page
    exit();
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM poste WHERE id = :id");
    $stmt->execute([':id' => $_GET['delete_id']]);
    header("Location: gestion-content.php");
    exit();
}
?>
