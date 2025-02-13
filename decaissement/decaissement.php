<?php
// Include the database connection file
include '../database.php'; // Adjust the path as needed

// Fetch postes from the database
try {
    // Query to fetch 'Nom et Prenom' where 'poste' is 'Caissière'
    $query = "SELECT `Nom et Prenom` FROM `poste` WHERE `poste` = 'Caissière' ORDER BY `Nom et Prenom` ASC";
    $stmt = $pdo->prepare($query);  // Prepare the query
    $stmt->execute();  // Execute the query

    // Fetch results
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC); // Get the data as an associative array
} catch (PDOException $e) {
    // In case of an error, display the error message and stack trace
    echo "Error: " . $e->getMessage();
    print_r($e->getTrace()); // Show detailed trace for debugging
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../images/apple-touch-icon.png">
    <title>Décaissement - Formulaire</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 20px;
            background-color: #f4f4f9;
            color: #333333;
            font-size: 1.4rem;
            font-family: georgia;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2rem;
            margin-top: 40px;
        }

        .dropdown {
            display: flex;
            align-items: center;
            margin-bottom: -10px;
            font-size: 1.9rem;
        }

        .dropdown .fas {
            margin-right: 10px;
            color: #555;
            transition: color 0.3s ease;
        }

        .dropdown select {
            padding: 15px;
            font-size: 1.4rem;
            border: 1px solid #ccc;
            background-color: rgb(8, 67, 74);
            color: rgb(255, 255, 255);
            appearance: none;
            border-radius: 14px;
        }

        .dropdown select:hover,
        .dropdown select:focus {
            outline: none;
            border-color: #4CAF50;
        }


        table {
            width: 95%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
            margin-left: 42px;
        }

        th, td {
            padding: 20px 25px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 1.4rem;
        }

        th {
            background-color: rgb(8, 67, 74);
            color: white;
            font-size: 1.5rem;
        }

        tr:nth-child(even) {
            background-color: #d9e5e8;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1.2rem;
        }

        .add-button {
            width: 240px;
            margin-top: 20px;
            padding: 15px 15px;
            font-size: 1.2rem;
            background-color: #155363;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 42px;
        }

        .add-button:hover {
            background-color: #10596a;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .action-button {
            padding: 10px 20px;
            font-size: 1.2rem;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .action-button:hover {
            transform: scale(1.1);
        }

        .back-button {
            background-color: #666;
        }

        .back-button:hover {
            background-color: #555;
        }

        .submit-button {
            background-color: darkgreen;
        }

        .submit-button:hover {
            background-color: #256d29;
        }
    </style>
</head>
<body>

<form action="submitdecaissement.php" method="post">
    <h2><i class="fas fa-file-alt"></i> Demande de décaissement</h2>
    <!-- Dropdown selection for Chef with dynamic options from the database -->
    <div class="dropdown">
        <i class="fas fa-user-circle"></i>
        <select name="requester_role" id="requester_role" required>
            <option value="" disabled selected>-- Nom du Demandeur --</option>
            <?php
            // Generate options from the fetched 'Nom et Prenom' values
            foreach ($result as $row) {
                echo "<option value='" . htmlspecialchars($row['Nom et Prenom'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($row['Nom et Prenom'], ENT_QUOTES, 'UTF-8') . "</option>";
            }
            ?>
        </select>
    </div>

    <table>
    <thead>
        <tr>
            <th>#</th>
            <th>Désignation</th>
            <th>Nom et Prénom du bénéficiaire</th>
            <th>Montant</th>
        </tr>
    </thead>
    <tbody id="itemRows">
        <tr>
            <td>1</td>
            <td><input type="text" name="designation[]" required></td>
            <td><input type="text" name="nom_et_prenom[]" required></td>
            <td><input type="number" name="montant[]" required min="1" oninput="calculateTotal()"></td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" style="text-align: right; font-weight: bold;">TOTAL :</td>
            <td id="totalAmount" style="font-weight: bold;">0.00</td>
        </tr>
        <tr>
    <td colspan="3" style="text-align: right; font-family:Georgia; font-weight: bold;">Total en lettres :</td>
    <td>
        <input type="text" id="totalInWords" name="total_in_words" placeholder="Saisir le total en lettres" style="width: 100%;" required>
    </td>
</tr>

    </tfoot>
    </table>

    <button class="add-button" type="button" onclick="addRow()">Ajouter une ligne<i class="fas fa-plus"></i></button>

    <div class="action-buttons">
        <button class="action-button back-button" type="button" onclick="window.history.back();">Précédent <i class="bi bi-arrow-left"></i></button>
        <button class="action-button submit-button" type="submit">Soumettre <i class="fas fa-paper-plane"></i></button>
    </div>
</form>
<script>
function addRow() {
    const tableBody = document.getElementById('itemRows');
    const rowCount = tableBody.getElementsByTagName('tr').length + 1;
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td>${rowCount}</td>
        <td><input type="text" name="designation[]" required></td>
        <td><input type="text" name="nom_et_prenom[]" required></td>
        <td><input type="number" name="montant[]" required min="1" oninput="calculateTotal()"></td>
        <td class="remove-cell">
            <button type="button" class="remove-button" onclick="removeRow(this)">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    tableBody.appendChild(newRow);
}

function removeRow(button) {
    const row = button.parentNode.parentNode;
    const isConfirmed = confirm("Etes-vous sûr de vouloir supprimer cette ligne ?");
    if (isConfirmed) {
        row.parentNode.removeChild(row);
        calculateTotal();
    }
}



    // Function to calculate the total
    function calculateTotal() {
        const montantInputs = document.querySelectorAll('input[name="montant[]"]');
        let total = 0;

        montantInputs.forEach(input => {
            const value = parseFloat(input.value) || 0;
            total += value;
        });

        document.getElementById('totalAmount').textContent = total.toFixed(2);
    }
</script>
<style>
    .remove-cell {
        text-align: center; /* Center-align the remove button in its cell */
        vertical-align: middle; /* Align vertically */
        width: 50px; /* Set a fixed width for the delete column */
    }

    .remove-button {
        padding: 5px 10px;
        color: red;
        border: none;
        border-radius: 50%; /* Circle shape for the button */
        font-size: 2rem;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 20px;
    }

    .remove-button i {
        pointer-events: none; /* Ensure only the button itself is clickable */
    }

    .remove-button:hover {
        background-color:rgb(176, 191, 183); /* Darker red on hover */
        transform: scale(1.1); /* Slightly increase size on hover */
    }

    .remove-button:focus {
        outline: none; /* Remove default focus outline */
        box-shadow: 0 0 4pxrgb(134, 22, 9); /* Subtle shadow for focus */
    }
</style>





</body>
</html>
