<?php
// Check if the redirect URL is passed in the query string
$redirect_url = isset($_GET['redirect_url']) ? $_GET['redirect_url'] : '/default.php'; // Fallback to default.php if not set

// If you want to make sure the base path is correct, you can append it to the $redirect_url.
$base_path = '/G_Request/'; // Base path for your project
$redirect_url = $base_path . ltrim($redirect_url, '/'); // Ensure no extra slashes
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation</title>
    <link rel="stylesheet" href="path/to/fontawesome/css/all.min.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .confirmation-message {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .icon {
            font-size: 2em;
            color: #4CAF50;
            margin-bottom: 10px;
        }
        .loader {
            margin: 20px auto;
            border: 6px solid #f3f3f3;
            border-top: 6px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <script>
        // Redirect after 3 seconds
        setTimeout(function() {
            window.location.href = "<?php echo $redirect_url; ?>";
        }, 2000);
    </script>
</head>
<body>
    <div class="confirmation-message">
        <i class="fas fa-check-circle icon"></i> <!-- Success icon -->
        <h1>Demande soumise avec succès !</h1>
        <p>Merci, votre demande a été envoyée et sera vérifiée bientôt.</p>
        <div class="loader"></div> <!-- Loader icon -->
    </div>
</body>
</html>
