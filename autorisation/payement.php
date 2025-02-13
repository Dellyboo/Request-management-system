<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autorisation de Paiement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../images/apple-touch-icon.png">
    <style>
        body {
            font-family: 'Georgia', serif;
            margin: 0;
            padding: 0;
            background-color:rgb(255, 255, 255);
            color: #333;
        }

        .header {
            background-color: rgb(8, 67, 74);
            color: white;
            text-align: center;
            padding: 35px 0;
            font-size: 1.9em;
            font-weight: bold;
        }

        .container {
            max-width: 900px;
            margin: -10px auto;
            background: white;
            padding: 60px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-top: -0px;
        }

        .form-group {
            margin-bottom: 40px;
        }

        .form-group label {
            font-weight: bold;
            font-size: 1.4rem;
            color: rgb(8, 67, 74);
            display: block;
            margin-top: -15px;
            padding-top: -20px;
            margin-bottom: 10px;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea {
            width: 100%;
            padding: 12px;
            font-size: 1.05rem;
            border: 2px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-group .checkbox-group {
            display: flex;
            gap: 20px;
            align-items: center;
            font-size: larger;
        }

        .form-group .checkbox-group input {
            margin-right: 1px;
        }

        .form-group .checkbox-group label {
            font-size: 1.23rem;
            margin-top: 7px;

        }

        input[type="checkbox"] {
            width: 20px; /* Set custom width */
            height: 20px; /* Set custom height */
            border-radius: 20px;
        }



        .submit-btn {
            width: 80%;
            padding: 25px;
            background-color: rgb(8, 67, 74);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }

        .submit-btn:hover {
            background-color: rgb(6, 50, 56);
            transform: scale(1.05);
        }

        .btn-container {
            display: flex;
            justify-content:center;
            gap: 10px;
        }

        .back-btn {
            background-color: #6c757d;
        }

        .back-btn:hover {
            background-color: #5a6268;
            transform: scale(1.05);
        }

        .btn-container a, .submit-btn {
            width: 20%;
            text-align: center;
        }

        .back-btn, .submit-btn {
            padding: 12px;
            font-size: 1.1rem;
            border-radius: 5px;
            color: white;
            text-decoration: none;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <i class="bi bi-file-earmark-text"></i>
        Autorisation de Payement
    </div>

    <div class="container">
    <form action="submit_paymentgrant.php" method="POST">
    <div class="form-group">
        <label for="beneficiaire"><i class="bi bi-person-fill"></i> Bénéficiaire :</label>
        <input type="text" id="beneficiaire" name="beneficiaire" placeholder="Nom du bénéficiaire" required>
    </div>

    <div class="form-group">
        <label for="motif"><i class="bi bi-clipboard-fill"></i> Motif de paiement :</label>
        <textarea id="motif" name="motif" placeholder="Entrez le motif de paiement" required></textarea>
    </div>

    <div class="form-group">
        <label for="montant"><i class="bi bi-currency-dollar"></i> Montant :</label>
        <input type="number" id="montant" name="montant" placeholder="Entrez le montant en Fbu" required>
    </div>

    <div class="form-group">
        <label for="facture"><i class="bi bi-receipt-cutoff"></i> Numéro de la facture :</label>
        <input type="text" id="facture" name="facture" placeholder="Entrez le numéro de facture" required>
    </div>

    <div class="form-group">
        <label><i class="bi bi-option"></i> Modes de règlement :</label>
        <div class="checkbox-group">
            <input type="checkbox" id="caisse" name="mode[]" value="caisse">
            <label for="caisse">Caisse</label>
        </div>
        <div class="checkbox-group">
            <input type="checkbox" id="virement" name="mode[]" value="virement">
            <label for="virement">Virement (OV)</label>
        </div>
        <div class="checkbox-group">
            <input type="checkbox" id="cheque" name="mode[]" value="cheque">
            <label for="cheque">Chèque</label>
        </div>
    </div>

    <div class="btn-container">
        <a href="javascript:history.back()" class="back-btn"><i class="bi bi-arrow-left"></i> Précédent</a>
        <button type="submit" class="submit-btn">Soumettre <i class="bi bi-send"></i></button>
    </div>
</form>

    </div>
</body>
</html>
