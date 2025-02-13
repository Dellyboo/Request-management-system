<?php
// database.php (Make sure this file is included in your form page)
$servername = "localhost"; // your server name
$username = "root"; // your database username
$password = ""; // your database password
$dbname = "konteldb"; // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch unique data from poste table sorted alphabetically
$sql_poste = "SELECT DISTINCT poste FROM poste ORDER BY poste ASC"; // Added DISTINCT keyword
$result_poste = $conn->query($sql_poste);

// Store the fetched unique data in an array
$postes = [];
if ($result_poste->num_rows > 0) {
    while ($row = $result_poste->fetch_assoc()) {
        $postes[] = $row['poste'];
    }
}

// Fetch unique names from employeenames table
$sql_employeenames = "SELECT DISTINCT names FROM employeenames ORDER BY names ASC"; // Added DISTINCT keyword
$result_employeenames = $conn->query($sql_employeenames);

// Store the fetched unique names in an array
$employeeNames = [];
if ($result_employeenames->num_rows > 0) {
    while ($row = $result_employeenames->fetch_assoc()) {
        $employeeNames[] = $row['names'];
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frais de Deplacement</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="/G_Request/images/apple-touch-icon.png">
    <style>
        body {
        font-family: 'Georgia', sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f9f9f9;
        color: #333;
        font-size: 16px;
        width: 100%;
        min-height: 100vh; /* Ensure body is at least 100vh */
        display: flex;
        align-items: center;
        justify-content: center;
    }

    form {
        width: 67%;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        padding: 55px;
        min-height: 100vh; /* Set minimum height to fill the viewport */
        box-sizing: border-box;
        margin-top: -55px;
    }



        h2 {
            text-align: center;
            margin: 35px 0;
            font-size: 1.9rem;
            color: rgb(4, 34, 37); /* Dark green color */
        }

        h3 {
            margin-top: 30px;
            margin-bottom: 10px;
            padding: 15px;
            background-color: rgb(8, 67, 74); /* Green color */
            color: white;
            border-radius: 5px;
            font-size: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        

        .input-group {
            margin-bottom: 15px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            
        }

        .input-field {
            flex: 1;
            margin-right: 25px;
            position: relative;
        }

        .input-field:last-child {
            margin-right: -60;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            font-size: 1.2rem;
            color: #155363; /* Dark green color */
        }

        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            margin-top: 5px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus,
        textarea:focus {
            border-color: #006600; /* Darker green on focus */
            outline: none;
        }

        textarea {
            height: 80px;
            resize: vertical;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            margin-bottom: -10px;
        }

        .action-button {
            padding: 12px 20px;
            font-size: 1.2rem;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 0 10px;
            transition: background-color 0.3s, transform 0.2s;
        }

        .submit-button {
            background-color: #007700; /* Green */
        }

        .submit-button:hover {
            background-color: #005500; /* Darker green */
            transform: scale(1.05);
        }

        .back-button {
            background-color: #555;
        }

        .back-button:hover {
            background-color: #444;
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .input-group {
                flex-direction: column;
            }

            .input-field {
                margin-right: 0;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>

<form action="submit_transportfee.php" method="post">
    <h2><i class="fas fa-file-alt"></i> Formulaire de Demande des frais de Deplacement</h2>
    
    <!-- Personnel Information -->
    <h3>Renseignements Du Personnel:</h3>
    <div class="input-group">
    <div class="input-field">
    <label><i class="fas fa-user"></i> Nom et prénom:</label>
    <select name="nom" required>
        <option value="">Sélectionnez un nom</option>
        <?php
        // Check if there are employee names and output them as options
        if (!empty($employeeNames)) {
            foreach ($employeeNames as $name) {
                echo "<option value='" . htmlspecialchars($name) . "'>" . htmlspecialchars($name) . "</option>";
            }
        } else {
            echo "<option value=''>Aucun nom disponible</option>";
        }
        ?>
    </select>
</div>
    
    </div>
    <div class="input-group">
        <div class="input-field">
            <label><i class="fas fa-briefcase"></i> Poste:</label>
            <select name="poste" required>
                <option value="" disabled selected>Select Poste</option>
                <?php foreach ($postes as $poste): ?>
                    <option value="<?php echo htmlspecialchars($poste); ?>"><?php echo htmlspecialchars($poste); ?></option>
                <?php endforeach; ?>
            </select>
        </div>        
        <div class="input-field">
            <label><i class="fas fa-phone"></i> Numéro de téléphone:</label>
            <input type="text" name="telephone" placeholder="Numéro de téléphone" required>
        </div>
    </div>

    <!-- Travel Expenses Section -->
    <h3>Section Sur Les Frais De Deplacement:</h3>
    <div class="input-group">
        <div class="input-field">
            <label><i class="fas fa-map-signs"></i> Lieu de départ:</label>
            <input type="text" name="lieu_depart" placeholder="Lieu de départ" required>
        </div>
        <div class="input-field">
            <label><i class="fas fa-map-marker-alt"></i> Adresse de destination:</label>
            <input type="text" name="adresse_destination" placeholder="Adresse de destination" required>
        </div>
    </div>
    <div class="input-group">
        <div class="input-field">
            <label><i class="fas fa-comment-dots"></i> Motif de déplacement:</label>
            <textarea name="motif_deplacement" placeholder="Motif de déplacement" required></textarea>
        </div>
    </div>

    <div class="input-group">
        <div class="input-field">
            <label><i class="fas fa-check-circle"></i> Aller:</label>
            <select name="aller" required>
                <option value="" disabled selected>-- Choisir --</option>
                <option value="Oui">Oui</option>
                <option value="Non">Non</option>
            </select>
        </div>
        <div class="input-field">
            <label><i class="fas fa-check-circle"></i> Aller-retour:</label>
            <select name="aller_retour" required>
                <option value="" disabled selected>-- Choisir --</option>
                <option value="Oui">Oui</option>
                <option value="Non">Non</option>
            </select>
        </div>
        <div class="input-field">
            <label><i class="fas fa-taxi"></i> Taxi:</label>
            <select name="taxi" required>
                <option value="" disabled selected>-- Choisir --</option>
                <option value="Oui">Oui</option>
                <option value="Non">Non</option>
            </select>
        </div>
        <div class="input-field">
            <label><i class="fas fa-bus"></i> Transport en commun:</label>
            <select name="transport_commun" required>
                <option value="" disabled selected>-- Choisir --</option>
                <option value="Oui">Oui</option>
                <option value="Non">Non</option>
            </select>
        </div>
        <div class="input-field">
            <label><i class="fas fa-car"></i> Location voiture:</label>
            <select name="location_voiture" required>
                <option value="" disabled selected>-- Choisir --</option>
                <option value="Oui">Oui</option>
                <option value="Non">Non</option>
            </select>
        </div>
    </div>

    <div class="input-group">
        <div class="input-field">
            <label><i class="fas fa-money-bill-wave"></i> Montant demandé:</label>
            <input type="number" name="montant" placeholder="Montant demandé" required>
        </div>
    </div>

    <div class="action-buttons">
        <a href="javascript:history.back()" class="action-button back-button">
            Précédent <i class="bi bi-arrow-left"></i>
        </a>
        <button type="submit" class="action-button submit-button">
            Soumettre <i class="bi bi-send"></i>
        </button>
    </div>

</form>
</body>
</html>