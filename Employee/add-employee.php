<?php
session_start();
require_once '../database.php'; // Adjust the path to your database connection file

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve form data
    $nom = isset($_POST['nom']) ? trim($_POST['nom']) : null;
    $prenom = isset($_POST['prenom']) ? trim($_POST['prenom']) : null;
    $genre = isset($_POST['genre']) ? trim($_POST['genre']) : null;
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $telephone = isset($_POST['telephone']) ? trim($_POST['telephone']) : null;
    $naissance = isset($_POST['naissance']) ? trim($_POST['naissance']) : null;
    $poste = isset($_POST['poste']) ? trim($_POST['poste']) : null;
    $departement = isset($_POST['departement']) ? trim($_POST['departement']) : null;
    $password = isset($_POST['password']) ? trim($_POST['password']) : null;
    $nomPrenom = isset($_POST['nomPrenom']) ? trim($_POST['nomPrenom']) : null;

    // Validate required fields
    if (!$nom || !$prenom || !$genre || !$email || !$telephone || !$naissance || !$poste || !$departement || !$password || !$nomPrenom) {
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Enforce department mapping in the backend
    if (in_array($poste, ['Caissière', 'Comptable', 'Technicien de surface'])) {
        $departement = 'Comptabilité';
    } elseif ($poste === 'Chef de Service') {
        $departement = 'Administration';
    }

    try {
        // Insert into the database
        $stmt = $pdo->prepare("INSERT INTO poste (Nom, Prenom, Genre, email, telephone, naissance, poste, Departement, password, `Nom et Prenom`)
                               VALUES (:nom, :prenom, :genre, :email, :telephone, :naissance, :poste, :departement, :password, :nomPrenom)");
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':genre' => $genre,
            ':email' => $email,
            ':telephone' => $telephone,
            ':naissance' => $naissance,
            ':poste' => $poste,
            ':departement' => $departement,
            ':password' => $password,
            ':nomPrenom' => $nomPrenom,
        ]);

        $_SESSION['success_message'] = "Employee added successfully!";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    } catch (PDOException $e) {
        // Log the error and show a friendly message
        error_log("Database error: " . $e->getMessage());
        $_SESSION['error_message'] = "Failed to add the employee. Please try again.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
} else {
    $_SESSION['error_message'] = "Invalid request method.";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
