<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande de Fonds</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../images/apple-touch-icon.png">
    <style>
        body {
            font-family: 'Georgia', serif;
            margin: 0;
            padding: 0;
            background-color:rgb(255, 255, 255);
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 90vh;
            flex-direction: column;
        }

        /* Top header */
        .top-header {
            width: 100vw; /* Full viewport width */
            padding: 40px;
            background-color: rgb(8, 67, 74);
            text-align: center;
            color: white;
            font-size: 27px;
            font-weight: bold;
            margin-top: -60px;
        }

        .form-container {
            background: white;
            padding: 50px;
            width: 45%;
            box-shadow: 0 3px 55px rgba(0, 0, 0, 0.3);
        }

        h2 {
            text-align: center;
            font-size: 2rem;
            color: white;
            background: #08434A;
            padding: 15px;
            border-radius: 5px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
            font-size: 1.3rem;
            color: rgb(8, 67, 74);
        }

        input, select, textarea {
            width: 95%;
            padding: 15px;
            border: 2px solid #ccc;
            border-radius: 6px;
            font-size: 1.1rem;
            transition: 0.3s;
        }

        select {
            font-family: georgia;
            color: #333;
        }

        input:focus, select:focus, textarea:focus {
            border-color: #08434A;
            outline: none;
            box-shadow: 0 0 8px rgba(8, 67, 74, 0.3);
        }

        .action-buttons {
            display: flex;
            margin-top: 20px;
            justify-content: space-around;
        }

        .action-button {
            width: 25%;
            padding: 12px 20px;
            font-size: 1.1rem;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        .back-button {
            background: #6c757d;
            margin-right: -410px;
        }

        .back-button:hover {
            background-color: #5a6268;
            transform: scale(1.03);
        }

        .submit-button {
            background: rgb(8, 67, 74);
        }

        .submit-button:hover {
            background: rgb(6, 50, 56);
            transform: scale(1.03);
        }
    </style>
</head>
<body>
    <!-- Top Header -->
    <div class="top-header">
        <i class="bi bi-file-earmark-text"></i> Fiche de Demande des Fonds
    </div>


    <div class="form-container">
        <form action="insert_funds.php" method="post">
            <div class="form-group">
                <label for="nom-demandeur"><i class="bi bi-person-fill"></i> Nom du demandeur :</label>
                <select id="nom-demandeur" name="nom_demandeur" required>
                    <option value="" disabled selected>-- Sélectionnez le nom du demandeur --</option>
                    <option value="Bizimana Youssouf">Bizimana Youssouf</option>
                    <option value="Mugenzi Thierry">Mugenzi Thierry</option>
                    <option value="Nirema Edmond">Nirema Edmond</option>
                    <option value="Nkurunziza Esperance">Nkurunziza Esperance</option>
                </select>
            </div>

            <div class="form-group">
                <label for="poste-demandeur"><i class="bi bi-briefcase-fill"></i> Poste du demandeur</label>
                <input type="text" id="poste-demandeur" name="poste_demandeur" readonly required>
            </div>

            <div class="form-group">
                <label for="motif"><i class="bi bi-clipboard-fill"></i> Motif :</label>
                <input type="text" id="motif" name="motif" required>
            </div>

            <div class="form-group">
                <label for="montant-chiffre"><i class="bi bi-currency-dollar"></i> Montant demandé en chiffre (Fbu) :</label>
                <input type="number" id="montant-chiffre" name="montant_chiffre" required>
            </div>

            <div class="form-group">
                <label for="montant-lettre"><i class="fas fa-file-alt"></i> Montant demandé en toute lettre :</label>
                <textarea id="montant-lettre" name="montant_lettre" rows="2" required></textarea>
            </div>

            <div class="action-buttons">
                <button class="action-button back-button" type="button" onclick="window.history.back();">
                    <i class="bi bi-arrow-left"></i> Précédent
                </button>
                <button class="action-button submit-button" type="submit">
                    Soumettre <i class="bi bi-send"></i>
                </button>
            </div>
        </form>
    </div>

    <script>
        const posteData = {
            "Bizimana Youssouf": "Chef Comptable",
            "Mugenzi Thierry": "Directeur Technique et Exploitation",
            "Nirema Edmond": "Chef de Projet",
            "Nkurunziza Esperance": "Directeur Administratif et Financier"
        };

        document.getElementById("nom-demandeur").addEventListener("change", function () {
            document.getElementById("poste-demandeur").value = posteData[this.value] || "";
        });
    </script>
</body>
</html>
