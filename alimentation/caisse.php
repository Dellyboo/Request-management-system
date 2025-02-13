<?php
// Include the database connection
try {
    $dsn = "mysql:host=localhost;dbname=konteldb;charset=utf8mb4";
    $username = "root";
    $password = "";

    // Create a PDO instance
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch data from the database for the "Demandeur" dropdown
try {
    $query = "SELECT `Nom et Prenom` FROM poste WHERE poste IN ('Caissière', 'Caissiere')";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $posteData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alimentation de Caisse</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/G_Request/images/apple-touch-icon.png">
    <style>
        body {
            font-family: 'Georgia', sans-serif;
            margin: 0;
            padding: 0;
            background-color:rgb(253, 253, 253);
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        form {
            width: 62%;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 45px;
            box-sizing: border-box;
            height: 100%;
            margin-top: -80px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.93rem;
            color: rgb(4, 34, 37);
        }

        h3 {
            margin-top: 20px;
            margin-bottom: 15px;
            background-color: rgb(8, 67, 74);
            color: white;
            padding: 22px 15px;
            border-radius: 5px;
            font-size: 22px;
        }

        .input-group {
            margin-bottom: 25px;
            font-size: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            color:rgb(13, 79, 78);
        }

        input[type="text"],
        input[type="date"],
        select {
            width: 97%;
            padding: 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        input:focus,
        select:focus {
            border-color: #006600;
            outline: none;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 20px;
        }

        .btn {
            padding: 10px 20px;
            font-size: 1.2rem;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn.submit {
            background-color: #007700;
        }

        .btn.submit:hover {
            background-color: #005500;
            transform: scale(1.05);
        }

        .btn.previous {
            background-color: #6c757d;
        }

        .btn.previous:hover {
            background-color: #5a6268;
            transform: scale(1.05);
        }

        .btn i {
            margin-left: 8px;
        }
    </style>
</head>
<body>
<form action="submit_alimentation.php" method="POST" enctype="multipart/form-data">
    <h2><i class="fas fa-file-alt"></i> Demande d'Alimentation de Caisse</h2>
    <h3>Informations Générales:</h3>

    <div class="input-group">
        <label for="montant"><i class="fas fa-coins"></i> Montant en (chiffre):</label>
        <input type="text" id="montant" name="montant" placeholder="Entrez le montant en chiffre" required>
    </div>
    
    <div class="input-group">
        <label for="total-lettres"><i class="fas fa-list-ol"></i> Total en lettres:</label>
        <input type="text" id="total-lettres" name="total-lettres" placeholder="Entrez le montant en lettres" required>
    </div>

    <div class="input-group">
        <label for="motif"><i class="fas fa-info-circle"></i> Motif:</label>
        <select id="motif" name="motif" style="font-weight: 550; font-family:Garamond; font-size: 25px;" required>
            <option value="Alimentation caisse" selected>Alimentation caisse</option>
            <option value="Autres">Autres</option>
        </select>
    </div>

    <div class="input-group">
        <label for="date"><i class="fas fa-calendar-alt"></i> Date:</label>
        <input type="date" id="date" name="date" required>
    </div>

    <div class="input-group">
        <label for="demandeur"><i class="fas fa-user"></i> Demandeur (Caissière):</label>
        <select id="demandeur" name="demandeur" style="font-weight: bold; font-family:Garamond; font-size: 25px;" required>
            <!-- Populate options dynamically using PHP -->
            <?php foreach ($posteData as $poste): ?>
                <option value="<?= htmlspecialchars($poste['Nom et Prenom']) ?>">
                    <?= htmlspecialchars($poste['Nom et Prenom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="input-group">
        <label for="signature"><i class="fas fa-signature"></i> Signature (Image):</label>
        <input type="file" id="signature" name="signature" accept="image/*" required>
    </div>

    <div class="action-buttons">
        <!-- Précédent Button -->
        <a href="javascript:history.back()" class="btn previous">
            Précédent <i class="bi bi-arrow-left"></i>
        </a>
        <!-- Soumettre Button -->
        <button type="submit" class="btn submit">
            Soumettre <i class="fas fa-paper-plane"></i>
        </button>
    </div>
</form>

</body>
</html>
