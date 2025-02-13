<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "konteldb";
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = ""; 
$nomEtPrenomOptions = []; 
$loginSuccess = false; // New variable to track login success

// Fetch Nom et Prenom for the dropdown
$sql = "SELECT `Nom et Prenom` FROM poste ORDER BY `Nom et Prenom` ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $nomEtPrenomOptions[] = $row["Nom et Prenom"];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = strtolower(trim($_POST["email"]));
    $password = trim($_POST["password"]);
    $nomEtPrenom = $_POST["nomEtPrenom"];

    $email = $conn->real_escape_string($email);
    $password = $conn->real_escape_string($password);
    $nomEtPrenom = $conn->real_escape_string($nomEtPrenom);

    $sql = "SELECT * FROM poste WHERE email = '$email' AND `Nom et Prenom` = '$nomEtPrenom' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["nomEtPrenom"] = $user["Nom et Prenom"];
        $poste = $user["poste"];

        switch ($poste) {
            case "Directeur général":
                $redirectPage = "index.php";
                break;
            case "Directeur administratif et financier":
                $redirectPage = "daf.php";
                break;
            case "Directeur technique et exploitation":
                $redirectPage = "dte.php";
                break;
            case "Chef Comptable":
                $redirectPage = "service.php";
                break;
            case "Chef de Projet":
                $redirectPage = "projet.php";
                break;
            case "Caissière":
                $redirectPage = "caissiere.php";
                break;
            default:
                $redirectPage = "default.php";
        }

        $loginSuccess = true; // Set login success to true
        header("Refresh:1.8; url=$redirectPage");
    } else {
        $error = "User does not exist or incorrect password/role.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="images/apple-touch-icon.png">
    <style>
        /* Previous styles remain the same */
        /* ... */

        /* New styles for the loader */
        .loader-container {
            display: none;
            margin-top: -18px;
            text-align: center;
            padding: 20px;
        }

        .loader-container.show {
            display: block;
        }

        .loader {
            border: 4px solid #f3f3f3;
            border-radius: 50%;
            border-top: 4px solid rgb(8, 67, 74);
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .success-message {
            color: rgb(8, 67, 74);
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
            font-family: Georgia;
        }

        .error-message {
            color: #ff0000;
            margin-top: 15px;
            font-size: 16px;
            font-weight: bold;
        }

        /* Previous styles remain the same */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Garamond;
            background: linear-gradient(to bottom right, #ffffff 49.9%, transparent 49.9%, transparent 50.1%, #d5f5e3 50.1%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .auth-container {
            width: 100%;
            max-width: 725px;
            background: white;
            padding: 50px;
            text-align: center;
            animation: fadeIn 1.5s ease;
            position: relative;
            height: 690px;
            margin-top: -13px;
            border-radius: 40px;
            border-top: 2px solid rgba(0, 0, 0, 0.33); /* Stylish left border */
        }

        .top-container {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo-background {
            width: 170px; /* Set the width of the circle */
            height: 155px; /* Set the height of the circle to make it a perfect circle */
            background-color: #d5f5e3; /* Apply the background color */
            background-image: url('logo/logo_kontel.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center; /* Center the logo inside the circle */
            border-radius: 50%; /* Make the background circular */
            margin-top: -120px;
            margin-left: 220px;
            animation: bounce 6s infinite ease-in-out;
        }


        h2 {
            font-size: 28px;
            color:rgb(82, 81, 90);
            font-weight: 550;
            margin-bottom: 30px;
            padding-top: 25px;
            font-family: Georgia, 'Times New Roman', Times, serif;
            margin-top: 15px;
        }

        select {
            font-family: Garamond, serif;
            font-size: 40px;
            padding: 12px 15px 12px 40px;
            background-color: darkgreen;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
            height: 50px;
            transition: all 0.3s;
            color: black;
            padding-top: 20px;
        }

        select option {
            font-family: Garamond, serif;
            font-size: 22px;
        }

        select:focus {
            border-color: rgb(8, 67, 74);
            background-color: darkgreen;
            box-shadow: 0 0 10px rgba(8, 67, 74, 0.3);
        }

        .form-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 25px;
            width: 100%;
        }

        .form-group label {
            font-size: 22px;
            color: #020024;
            font-weight: 500;
            margin-bottom: 5px;
            font-weight: bold;
            margin-left: 23px;
        }

        .input-container {
            position: relative;
            width: 100%;
        }

        .input-container i {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: rgb(8, 67, 74);
            font-size: 21px;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"],
        select {
            width: 100%;
            padding: 15px 10px 12px 40px;
            font-size: 20px;
            height: 50px;
            border: 0.5px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            transition: all 0.7s;
            font-family: Garamond, serif;
        }

        input[type="text"]:focus,
        input[type="password"]:focus,
        input[type="email"]:focus,
        select:focus {
            border-color: rgb(8, 67, 74);
            background-color: #d5f5e3;
            box-shadow: 0 0 10px rgba(8, 67, 74, 0.3);
            border-radius: 10px;
        }

        .submit-button {
            width: 80%;
            padding: 16px;
            background-color: #1e8449;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 23px;
            cursor: pointer;
            transition: background-color 0.4s;
            font-weight: 500;
            margin-top: 25px;
            font-family: Garamond;
        }

        .submit-button:hover {
            background-color: #145a32;
        }

        footer {
            position: absolute;
            margin-top: 775px;
            width: 100%;
            text-align: center;
            font-family: Garamond;
            color: #888;
            font-size: 20px;
            padding: 10px 0;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .auth-container {
                padding: 20px;
            }

            .form-group label {
                font-size: 14px;
            }

            .submit-button {
                font-size: 14px;
            }
        }
        @keyframes bounce {
    0%, 100% {
        transform: scale(1); /* Normal size */
    }
    50% {
        transform: scale(1.1); /* Slightly larger size */
    }
}
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="logo-background"></div>
        <h2>Request Management System</h2>
        <form action="login.php" method="POST">
    <div class="form-group">
        <label for="nomEtPrenom">Sélectionnez votre profil:</label>
        <div class="input-container">
            <i class="fas fa-user"></i>
            <select id="nomEtPrenom" name="nomEtPrenom" required>
                <option value="">Choisissez ici:</option>
                <?php foreach ($nomEtPrenomOptions as $nomEtPrenom) : ?>
                    <option value="<?= htmlspecialchars($nomEtPrenom) ?>"><?= htmlspecialchars($nomEtPrenom) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="email">Email:</label>
        <div class="input-container">
            <i class="fas fa-envelope"></i>
            <input type="email" id="email" name="email" required placeholder="Entrez votre email">
        </div>
    </div>

    <div class="form-group">
        <label for="password">Mot de Passe:</label>
        <div class="input-container">
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" required placeholder="Entrez votre mot de passe">
        </div>
    </div>

    <button type="submit" class="submit-button">S'authentifier</button>

    <!-- Loader and success message container -->
    <div class="loader-container <?php echo $loginSuccess ? 'show' : ''; ?>">
        <div class="loader"></div>
        <div class="success-message">Connexion réussie! Redirection en cours...</div>
    </div>

    <?php if (!empty($error)) : ?>
        <p class="error-message"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
</form>
</div>

<footer>
    <p> Request Management System. &copy; 2025 Kontel Sa. All Rights Reserved.</p>
</footer>

</body>
</html>