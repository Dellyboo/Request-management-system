<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require '../database.php';

    // Fetch form data
    $montant = $_POST['montant'] ?? '';
    $total_lettres = $_POST['total-lettres'] ?? '';
    $motif = $_POST['motif'] ?? '';
    $date = $_POST['date'] ?? '';
    $demandeur = $_POST['demandeur'] ?? '';

    // Handle signature file upload
    $signaturePath = '';
    if (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/signatures/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $signaturePath = $uploadDir . basename($_FILES['signature']['name']);
        move_uploaded_file($_FILES['signature']['tmp_name'], $signaturePath);
    }

    // Insert data into the database
    $stmt = $pdo->prepare("
        INSERT INTO `Alimentation de Caisse` (montant, total_lettres, motif, date, demandeur, signature)
        VALUES (:montant, :total_lettres, :motif, :date, :demandeur, :signature)
    ");
    $stmt->execute([
        ':montant' => $montant,
        ':total_lettres' => $total_lettres,
        ':motif' => $motif,
        ':date' => $date,
        ':demandeur' => $demandeur,
        ':signature' => $signaturePath,
    ]);

    echo "Form data successfully submitted!";
    // Redirect or display a success message
    header('Location: confirmation1.php'); // Change to your desired success page
    exit();
}
?>
