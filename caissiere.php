<?php
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Database connection
require 'database.php';

// Initialize variables
$demandes = [];

// Fetch the name from the 'employeenames' table
try {
    $stmt = $pdo->prepare("SELECT names FROM employeenames WHERE id = 18");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $name = $row['names'];
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}

// Create the PDO connection (assumes the connection is established in 'database.php')
try {
    // Prepare the SQL query to fetch data from the 'alimentation de caisse' table
    $stmt = $pdo->prepare("SELECT id, montant, total_lettres, motif, date, demandeur, signature, status FROM `alimentation de caisse`");
    $stmt->execute();

    // Fetch all rows as an associative array
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching data: " . $e->getMessage();
    $demandes = [];
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/x-icon" href="images/apple-touch-icon.png">
    <title>Gestion de Demande</title>
    <style>
        
body {
    background-color: white; /* Dark background for the whole page */
    color: #ffffff; /* White text for better contrast */
}

/* Add button styles */
.action-button {
    padding: 6px 12px;
    color: #fff;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    margin-right: 5px;
    width: 100px;
    height: 40px;
    font-size: 17px;
}
.approve-btn {
    background-color: #8B8000; /* Green */
}
.delete-btn {
    background-color: #f44336; /* Red */
}
.status-approved {
    color: green; /* Yellow for déjà approuvé */
}
.scrollable-table {
    overflow-x: auto; /* Enable horizontal scrolling */
}
.confirmation-message {
    display: none; /* Hidden by default */
    color: red; /* Red color for confirmation message */
}

table {
    width: 93%;
    margin-top: 30px;
    margin-left: 65px;
    overflow: hidden; /* To clip the corners */
    animation: fadeIn 1s ease; /* Animation for fade in */
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

th {
    background-color: #10596a; /* Sky dark blue */
    color: white;
    padding: 14px; /* Increased padding for header */
    font-size: 1.4em; /* Increased font size for header */
    text-align: left; /* Align text to left */
}

td {
    padding: 15px; /* Increased padding for cells */
    border: 1px solid #ddd; /* Light border for cells */
    transition: background-color 0.3s; /* Transition for hover effect */
    font-size: 1.3em;
    color: black;
}

tr:nth-child(even) {
    background-color: whitesmoke; /* Dark background for even rows */
}

tr:hover {
    background-color: burlywood; /* Highlight row on hover */
}

tr:last-child td {
    border-bottom: none; /* Remove border from last row */
}

/* Added styles for scrolling feature */
.scrollable-table {
    max-height: 800px; /* Set a max height for the table container */
    overflow-y: auto; /* Enable vertical scrolling */
    border: 1px solid #ddd; /* Optional: Add border around the scrollable area */
}
    </style>

</head>
<body>
    <div class="dashboard-container">
<aside class="sidebar">
    <a href="caissiere.php">
        <div class="logo-container">
            <img src="logo/logo_kontel1.png" alt="Logo Kontel" class="logo bounce" />
        </div>
    </a>
    <h2>Caissière</h2>
    <ul>
        <li onmouseover="addBounceEffect(this)" onmouseout="removeBounceEffect(this)" onclick="navigateTo('dashboard')" class="nav-link" data-tab="dashboard">
            <i class="fas fa-home"></i> Dashboard
        </li>
        <li onmouseover="addBounceEffect(this)" onmouseout="removeBounceEffect(this)" onclick="navigateTo('status')" class="nav-link" data-tab="status">
            <i class="bi bi-bag-check-fill"></i> Status du demande
        </li>
        <li onmouseover="addBounceEffect(this)" onmouseout="removeBounceEffect(this)" onclick="navigateTo('about')" class="nav-link" data-tab="about">
            <i class="fas fa-info-circle"></i> A propos du logiciel
        </li>
        <li onmouseover="addBounceEffect(this)" onmouseout="removeBounceEffect(this)" onclick="confirmLogout()" class="nav-link" data-tab="logout">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </li>
    </ul>
</aside>


        <main class="content">
        <div id="dashboard-content" class="content-section active">
        <h1>
            Bienvenue, <?php echo htmlspecialchars($name); ?>! <i class="fas fa-grin-beam-sweat" style="font-size:25px; color:yellow"></i>
        </h1>
    <div class="clickable-boxes">
        <div onclick="navigateTo('form')" class="box">
            <i class="fas fa-file-alt"></i> Formulaires de Demande
        </div>
        <div onclick="navigateTo('status')" class="box">
            <i class="bi bi-hourglass-bottom"></i> Status du demande
        </div>
        <div onclick="navigateTo('parametres')" class="box">
            <i class="fas fa-cogs"></i> Paramètres
        </div>
    </div>
</div>




<?php
// Include database connection
require 'database.php';

// Fetch data from 'decaissement' table where 'poste_demandeur' is 'Caissière'
$query = $pdo->prepare("SELECT id, motif_de_decaissement, nom_prenom_beneficiaire, montant, total, total_en_lettres, date, nom_prenom_demandeur, poste_demandeur, status FROM decaissement WHERE poste_demandeur = ?");
$query->execute(['Caissière']);
$decaissements = $query->fetchAll(PDO::FETCH_ASSOC);
?>


<div id="status-content" class="content-section" style="overflow-y: auto; max-height: 90vh;">
    <h1>Status du demande</h1>

    <div class="section">
    <h2 class="toggle-subtitle" onclick="toggleContent('decaissement')">
        <i class="bi bi-file-earmark-minus-fill"></i> Pour le Décaissement
        <span class="toggle-icon">+</span> <!-- + sign initially -->
    </h2>
    <div id="decaissement" class="content" style="display: none;">
        <div class="confirmation-message" id="confirmation-message"></div>
        <div class="scrollable-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom complet du demandeur</th>
                        <th>Rôle du demandeur</th>
                        <th>Motif</th>
                        <th>Nom du bénéficiaire</th>
                        <th>Montant</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($decaissements)): ?>
                        <?php foreach ($decaissements as $decaissement): ?>
                            <tr id="row-<?php echo $decaissement['id']; ?>">
                                <td><?php echo htmlspecialchars($decaissement['id']); ?></td>
                                <td><?php echo htmlspecialchars($decaissement['nom_prenom_demandeur']); ?></td>
                                <td><?php echo htmlspecialchars($decaissement['poste_demandeur']); ?></td>
                                <td><?php echo htmlspecialchars($decaissement['motif_de_decaissement']); ?></td>
                                <td><?php echo htmlspecialchars($decaissement['nom_prenom_beneficiaire']); ?></td>
                                <td><?php echo htmlspecialchars($decaissement['montant']); ?></td>
                                <td><?php echo htmlspecialchars($decaissement['total']); ?></td>
                                <td><?php echo htmlspecialchars($decaissement['date']); ?></td>
                                <td id="status-<?php echo $decaissement['id']; ?>" 
                                    class="<?php 
                                        echo ($decaissement['status'] === 'déjà approuvé') 
                                            ? 'status-approved' 
                                            : (in_array($decaissement['status'], ['en cours de vérification (actuellement au DA&F)', 'en cours de vérification (actuellement au DG)']) 
                                                ? 'status-in-progress' 
                                                : ''); 
                                    ?>">
                                    <?php echo htmlspecialchars($decaissement['status']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10">Aucune donnée trouvée pour l'alimentation de caisse.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="divider"></div>
</div>


<!-- Subtitle "Pour l'Alimentation de Caisse" -->
<div class="section">
    <h2 class="toggle-subtitle" onclick="toggleContent('autorisation')">
        <i class="bi bi-file-earmark-plus-fill"></i> Pour l'alimentation de caisse
        <span class="toggle-icon">+</span> <!-- + sign initially -->
    </h2>
    <div id="autorisation" class="content" style="display: none;">
        <div class="confirmation-message" id="confirmation-message"></div>
        <div class="scrollable-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Montant</th>
                        <th>Total (en lettres)</th>
                        <th>Motif</th>
                        <th>Date</th>
                        <th>Demandeur</th>
                        <th>Signature</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($demandes)): ?>
                        <?php foreach ($demandes as $demande): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($demande['id']); ?></td>
                                <td><?php echo htmlspecialchars($demande['montant']); ?></td>
                                <td><?php echo htmlspecialchars($demande['total_lettres']); ?></td>
                                <td><?php echo htmlspecialchars($demande['motif']); ?></td>
                                <td><?php echo htmlspecialchars($demande['date']); ?></td>
                                <td><?php echo htmlspecialchars($demande['demandeur']); ?></td>
                                <td>
                                    <?php if (!empty($demande['signature'])): ?>
                                <img src="<?php echo 'http://localhost/G_Request/alimentation/' . htmlspecialchars($demande['signature']); ?>" 
                                          alt="Signature" style="max-width: 100px; height: auto;">
                                    <?php else: ?>
                                              N/A
                                    <?php endif; ?>
                                </td>
                                <td class="<?php echo ($demande['status'] === 'déjà approuvé') 
                                    ? 'status-approved' 
                                    : (strpos($demande['status'], 'en cours de vérification') !== false 
                                    ? 'status-in-progress' 
                                    : ''); ?>">
                                    <?php echo htmlspecialchars($demande['status']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">Aucune donnée trouvée.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="divider"></div>
</div>

<script>
function toggleContent(contentId) {
    const content = document.getElementById(contentId);
    const icon = content.previousElementSibling.querySelector('.toggle-icon'); // Get the icon element next to the title

    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.innerText = '×'; // Change the icon to '×' when content is shown
    } else {
        content.style.display = 'none';
        icon.innerText = '+'; // Change the icon back to '+' when content is hidden
    }
}

function handleAction(action, id) {
    if (action === 'approve') {
        if (confirm('Êtes-vous sûr de vouloir transmettre cette demande?')) {
            // Update status in the database
            fetch('update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id, status: 'en cours de vérification' }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById(`status-${id}`).innerText = 'en cours de vérification';
                    document.getElementById(`status-${id}`).classList.remove('status-approved'); // Remove previous class
                    document.getElementById(`status-${id}`).classList.add('status-in-progress'); // Add new class
                    showConfirmationMessage('Demande approuvée avec succès.');
                } else {
                    showConfirmationMessage('Erreur lors de l\'approbation de la demande.');
                }
            });
        }
    } else if (action === 'delete') {
        if (confirm('Êtes-vous sûr de vouloir refuser cette demande?')) {
            // Delete item from the database
            fetch('delete_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById(`row-${id}`).remove();
                    showConfirmationMessage('Demande refusée avec succès.');
                } else {
                    showConfirmationMessage('Erreur lors de la suppression de la demande.');
                }
            });
        }
    }
}

function showConfirmationMessage(message) {
    const confirmationMessage = document.getElementById('confirmation-message');
    confirmationMessage.innerText = message;
    confirmationMessage.style.display = 'block';
    setTimeout(() => {
        confirmationMessage.style.display = 'none';
    }, 3000);
}
</script>
</div>
<style>
    /* Styles for status colors */
    .status-in-progress {
        color: yellow; 
}

.status-pending {
    color: black; /* Pending status in black */
}

.status-approved {
    color: green;
}

.status-undefined {
        color: yellow; 
}

    .status-approved {
        color: green; /* Already approved status in green */
    }
    
    .status-in-progress {
        color: yellow; /* In progress status in dark yellow */
        background-color:rgb(32, 106, 64);
        border-radius: 10px;
    }

    .toggle-subtitle {
    cursor: pointer;
    font-weight: bold;
    margin: 50px 0 10px;
    display: flex;
    justify-content: space-between; /* Spread the content to both ends */
    align-items: left; /* Align the content vertically */
    }

    .toggle-icon {
    font-size: 18px;
    margin-left: auto;
    flex-shrink: 0; /* Prevent the icon from shrinking */
}
.toggle-subtitle i {
    margin-right: 8px; /* Space between the icon and text */
}

    .divider {
        border-top: 1px solid rgb(8, 67, 74);
        margin-top: 15px;
        margin-left: 60px;
    }

    .section {
        margin-bottom: 20px;
    }
    h2 i {
        margin-left:60px;
    }

</style>


<!-- CSS for Conditional Styling -->
<style>
    /* Basic styling for table */
    .content-section {
            padding: 10px;
        }
        
        table {
            width: 94%;
            
            
        }
        th, td {
            padding: 14px;
            text-align: left;
        }

        /* Style for the export button */
        .export-button {
            padding: 12px 15px;
            background-color: darkgreen; /* Green color */
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-bottom: -5px;
            margin-left: 1300px;
            width: 208px;
        }
        .export-button:hover {
            background-color: #218838;
        }

    /* Status color styling */
    .status-pending { color: black; }
    .status-verification { color: darkgoldenrod; }
    .status-approved { color: green; }

    /* Button styling */
    .action-button.approve-btn.approved {
        background-color: green;
        color: white;
    }
</style>

<!-- JavaScript for Action Handling -->
<script>
function handleAction(action, id) {
    const confirmation = confirm(`Êtes-vous sûr de vouloir ${action === 'approve' ? 'approuver' : 'refuser'} cette demande?`);
    if (!confirmation) return;

    // AJAX request
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "actions.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                const statusCell = document.getElementById(`status-${id}`);
                const row = document.getElementById(`row-${id}`);

                if (action === "approve") {
                    statusCell.innerText = "déjà approuvé";
                    statusCell.classList.remove("status-pending", "status-verification");
                    statusCell.classList.add("status-approved");
                    showMessage("La demande a été approuvée.", "success");
                } else if (action === "delete") {
                    row.remove();
                    showMessage("La demande a été refusée.", "error");
                }
            } else {
                showMessage("Une erreur s'est produite. Veuillez réessayer.", "error");
            }
        }
    };

    xhr.send(`action=${action}&id=${id}`);
}

// Function to show a confirmation message
function showMessage(message, type) {
    const messageDiv = document.getElementById("confirmation-message");
    messageDiv.innerText = message;
    messageDiv.className = `confirmation-message ${type}`;

    setTimeout(() => {
        messageDiv.innerText = "";
        messageDiv.className = "confirmation-message";
    }, 3000); // Message disappears after 3 seconds
}
</script>


<div id="parametres-content" class="content-section">
    <h1>Paramètres</h1>
    
    <!-- Profile Picture Section -->
    <div class="profile-picture-section">
        <?php
        // Fetch profile picture and user details from database
        $userId = 18; // User ID
        $pdo = new PDO("mysql:host=localhost;dbname=konteldb", "root", ""); // Database connection
        $stmt = $pdo->prepare("SELECT * FROM poste WHERE id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $picture = $row['picture'] ? $row['picture'] : 'default.png'; // Use default.png if no picture exists
        ?>
        <!-- Display profile picture -->
        <div class="profile-picture-wrapper">
            <img src="images/<?php echo $picture; ?>" alt="Profile Picture" class="profile-picture">
            <!-- Transparent Change Photo Button -->
            <div class="change-photo">
                <form id="upload-photo-form" action="upload-profile-picture.php" method="POST" enctype="multipart/form-data">
                    <input type="file" name="profile_picture" id="upload-photo" accept="image/*" onchange="document.getElementById('upload-photo-form').submit();" hidden>
                    <button type="button" onclick="document.getElementById('upload-photo').click();" class="change-photo-btn">
                        <i class="bi bi-camera-fill"></i> 
                    </button>
                    <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                </form>
            </div>
        </div>

        <!-- User Information -->
        <div class="user-info">
            <div class="user-info-row">
                <div class="user-info-column">
                    <i class="bi bi-person-fill"></i> <strong>ID:</strong> <?php echo $row['id']; ?>
                </div>
                <div class="user-info-column">
                    <i class="bi bi-person-lines-fill"></i> <strong>Nom:</strong> <?php echo $row['Nom et Prenom']; ?>
                </div>
                <div class="user-info-column">
                    <i class="bi bi-telephone-fill"></i> <strong>Téléphone:</strong> <?php echo $row['telephone']; ?>
                </div>
                <div class="user-info-column">
                    <i class="bi bi-briefcase-fill"></i> <strong>Poste:</strong> <?php echo $row['poste']; ?>
                </div>
            </div>
            <hr>
            <div class="user-info-row">
                <div class="user-info-column">
                    <i class="bi bi-gender-ambiguous"></i> <strong>Genre:</strong> <?php echo $row['Genre']; ?>
                </div>
                <div class="user-info-column">
                    <i class="bi bi-envelope-fill"></i> <strong>Email:</strong> <?php echo $row['email']; ?>
                </div>
                <div class="user-info-column">
                    <i class="bi bi-calendar-event-fill"></i> <strong>Naissance:</strong> <?php echo $row['naissance']; ?>
                </div>
                <div class="user-info-column">
                    <i class="bi bi-building-fill"></i> <strong>Département:</strong> <?php echo $row['Departement']; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Options -->
    <div class="settings-options-container">
        <div class="settings-option edit-profile" onclick="openPopup('profile-popup')">
            <i class="fas fa-user-edit"></i>
            <span>Modifier les informations personnelles</span>
        </div>
        <div class="settings-option change-password" onclick="openPopup('password-popup')">
            <i class="fas fa-key"></i>
            <span>Changer le mot de passe</span>
        </div>
    </div>
</div>

<!-- CSS for Styling -->
<style>
    .profile-picture-section {
        text-align: center;
        margin-bottom: 20px;
        position: relative;
        display: inline-block;
    }
    .profile-picture-wrapper {
        position: relative;
        display: inline-block;
    }
    .profile-picture {
        width: 250px;
        height: 250px;
        border-radius: 50%;
        border: 2px solid #ddd;
        object-fit: cover;
        margin: 25px auto;
        margin-left: 220px;
    }
    .change-photo {
        position: absolute;
        bottom: 15px;
        left: 50%;
        transform: translateX(-50%);
        text-align: center;
    }
    .change-photo-btn {
        background-color: rgb(0, 0, 0);
        color: #fff;
        border: none;
        border-radius: 20px;
        padding: 10px 35px;
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 5px;
        opacity: 0;
        transition: opacity 0.3s;
        margin-left: 220px;
        margin-top: -70px;
    }
    .profile-picture-wrapper:hover .change-photo-btn {
        opacity: 1;
    }
    .change-photo-btn i {
        font-size: 25px;
    }
    .change-photo-btn:hover {
        background-color: rgb(10, 80, 88);
    }
    .user-info i {
        color: rgb(28, 41, 43);
    }
    .user-info {
        margin-top: 20px;
        text-align: right;
        width: 100%;
        margin-left: 110px;
        font-size: 20px;
        color:rgb(33, 42, 41);
    }
    .user-info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .user-info-column {
        flex: 1;
        padding: 10px;
        border-right: 1px solid rgba(8, 67, 74, 0.73);
        text-align: center;
    }
    .user-info-column:last-child {
        border-right: none;
    }
    hr {
        border: 0;
        border-top: 1px solid rgba(8, 67, 74, 0.71);
        margin: 10px 0;
    }
</style>



<?php
// Database connection
require 'database.php';

// Fetch data from the 'poste' table where id = 1
$stmt = $pdo->prepare("SELECT * FROM poste WHERE id = 18");
$stmt->execute();
$userData = $stmt->fetch();

// If no data is found, handle accordingly
if (!$userData) {
    die('Aucun utilisateur trouvé.');
}
?>

<!-- Pop-Up for Editing Profile -->
<div id="profile-popup" class="popup-form">
    <div class="popup-content">
        <h2><i class="bi bi-pencil-square"></i> Modifier les informations personnelles</h2>
        <form id="profile-form">
            <div class="form-container">
                <!-- Left Side Form Fields -->
                <div class="form-left">
                    <div class="form-group">
                        <label for="nom">
                            <i class="bi bi-person"></i> Nom:
                        </label>
                        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($userData['Nom']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="prenom">
                            <i class="bi bi-person"></i> Prenom:
                        </label>
                        <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($userData['Prenom']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="genre">
                            <i class="bi bi-gender-ambiguous"></i> Genre:
                        </label>
                        <input type="text" id="genre" name="genre" value="<?php echo htmlspecialchars($userData['Genre']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="bi bi-envelope"></i> Email:
                        </label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                    </div>
                </div>

                <!-- Right Side Form Fields -->
                <div class="form-right">
                    <div class="form-group">
                        <label for="telephone">
                            <i class="bi bi-telephone"></i> Téléphone:
                        </label>
                        <input type="text" id="telephone" name="telephone" value="<?php echo htmlspecialchars($userData['telephone']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="naissance">
                            <i class="bi bi-calendar-date"></i> Date de Naissance:
                        </label>
                        <input type="date" id="naissance" name="naissance" value="<?php echo htmlspecialchars($userData['naissance']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="poste">
                            <i class="bi bi-briefcase"></i> Poste:
                        </label>
                        <input type="text" id="poste" name="poste" value="<?php echo htmlspecialchars($userData['poste']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="departement">
                            <i class="bi bi-building"></i> Département:
                        </label>
                        <input type="text" id="departement" name="departement" value="<?php echo htmlspecialchars($userData['Departement']); ?>" required>
                    </div>
                </div>
            </div>

            <!-- Hidden input for ID -->
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($userData['id']); ?>">

            <div class="button-group">
                <button type="submit" class="update-btn">
                    <i class="bi bi-save"></i> Mettre à jour
                </button>
                <button type="button" class="close-btn" onclick="closePopup()">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- CSS Styling for Pop-Up Form -->
<style>
    /* Same styles as before for the form layout */
    .popup-form {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.567);
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .popup-content {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        width: 870px;
        text-align: left;
        font-size: 18.5px;
        margin-left: 350px;
    }

    .popup-content h2 {
        text-align: center;
        font-size: 1.5em;
        margin-bottom: 20px;
    }

    .form-container {
        display: flex;
        justify-content: space-between;
        gap: 40px;
    }

    .form-left, .form-right {
        width: 45%;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: bold;
        font-size: 1.1em;
    }

    .popup-content input {
        width: 100%;
        padding: 12px;
        margin: 10px 0;
        border-radius: 5px;
        border: 1px solid #ccc;
        font-size: 1em;
    }

    .button-group {
        display: flex;
        justify-content: space-between;
    }

    .update-btn {
        padding: 12px 20px;
        background: #064946;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        width: 48%;
    }

    .update-btn:hover {
        background-color: #052e27;
    }

    .close-btn {
        padding: 10px 10px;
        background: #e57373;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        width: 14%;
    }

    .close-btn:hover {
        background-color: #f44336;
    }
</style>

<!-- JavaScript to Open and Close Pop-Ups -->
<script>
    function openPopup() {
        document.getElementById('profile-popup').style.display = 'flex';
    }

    function closePopup() {
        document.getElementById('profile-popup').style.display = 'none';
    }

    // AJAX to submit form and show confirmation alert
    document.getElementById('profile-form').addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission

        const formData = new FormData(this);

        fetch('update-profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Show confirmation message after successful update
                alert('Les informations ont été mises à jour avec succès!');
                // Close the popup after confirmation
                closePopup();
            } else {
                alert('Erreur lors de la mise à jour des informations');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Une erreur est survenue. Veuillez réessayer.');
        });
    });
</script>

<!-- Pop-Up for Changing Password -->
<div id="password-popup" class="popup-form">
    <div class="popup-content">
        <h2><i class="bi bi-key"></i> Changer le mot de passe</h2>
        <form id="change-password-form" action="passwords/update-caissiere.php" method="POST">
            <div class="form-group">
                <label for="current-password">
                    <i class="bi bi-lock"></i> Mot de passe actuel:
                </label>
                <input type="password" id="current-password" name="current-password" required>
            </div>
            <div class="form-group">
                <label for="password">
                    <i class="bi bi-lock-fill"></i> Nouveau mot de passe:
                </label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm-password">
                    <i class="bi bi-lock-fill"></i> Confirmer le mot de passe:
                </label>
                <input type="password" id="confirm-password" name="confirm-password" required>
            </div>
            <div class="button-group">
                <button type="submit" class="update-btn">
                    <i class="bi bi-save"></i> Mettre à jour
                </button>
                <button type="button" class="close-btn" onclick="closePopup()">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </form>
    </div>
</div>


<script>

// Function to open the pop-up
function openPopup(popupId) {
    document.getElementById(popupId).style.display = 'flex';
}

// Function to close the pop-up
function closePopup() {
    document.querySelectorAll('.popup-form').forEach(popup => popup.style.display = 'none');
}

// Function to handle form submission and update user data
document.getElementById('change-password-form').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent the default form submission

    // Check if the passwords match
    var password = document.getElementById('password').value;
    var confirmPassword = document.getElementById('confirm-password').value;
    
    if (password !== confirmPassword) {
        alert('Les mots de passe ne correspondent pas');
        return; // Prevent form submission
    }

    var formData = new FormData(this);

    fetch('passwords/update-caissiere.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())  // Ensure the response is parsed as JSON
    .then(data => {
        if (data.status === 'success') {
            alert('Mot de passe mis à jour avec succès!');
            closePopup(); // Close the popup after success
        } else {
            alert('Erreur lors de la mise à jour: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue. Veuillez réessayer.');
    });
});


</script>



<?php
// Database connection
require 'database.php'; // Make sure this file includes your PDO connection setup

// Fetching data from the database
$query = $pdo->query("SELECT * FROM a_propos_du_logiciel");
$aProposContent = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="about-content" class="content-section">
    <h1>Gestion et Demande du matériel de consommation</h1>

    <?php
    // Loop through the fetched content and display it
    foreach ($aProposContent as $section) {
        echo "<h2>" . htmlspecialchars($section['section_title']) . "</h2>";
        echo "<p>" . nl2br(htmlspecialchars($section['content'])) . "</p>"; // Use nl2br to convert new lines to <br> tags
    }
    ?>
</div>





<div id="form-content" class="content-section">
    <h1>Formulaires de Demande</h1>
    <p></p>

    <div class="icon-grid">
    <a href="alimentation/caisse.php" class="icon-item">
        <div class="icon-box">
            <i class="bi bi-file-earmark-plus-fill"></i>
            <p>Alimentation de Caisse</p>
        </div>
    </a>
    <a href="decaissement/decaissement.php" class="icon-item">
        <div class="icon-box">
            <i class="bi bi-file-earmark-minus-fill"></i>
            <p>Décaissement</p>
        </div>
    </a>
    
</div>

        </main>
    </div>


    <script>
        function navigateTo(page) {
            if (page === 'form1.html') {
                window.location.href = page;
            } else {
                const sidebarLinks = document.querySelectorAll('.sidebar li');
                sidebarLinks.forEach(link => {
                    link.classList.remove('active');
                });

                const activeLink = Array.from(sidebarLinks).find(link => link.dataset.tab === page);
                if (activeLink) {
                    activeLink.classList.add('active');
                }

                const contentSections = document.querySelectorAll('.content-section');
                contentSections.forEach(section => {
                    section.classList.remove('active');
                });

                const activeSection = document.getElementById(page + '-content');
                if (activeSection) {
                    activeSection.classList.add('active');
                }
            }
        }

        function confirmLogout() {
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                window.location.href = 'logout.php';
            }
        }

        function addBounceEffect(element) {
            element.classList.add('bounce');
        }

        function removeBounceEffect(element) {
            element.classList.remove('bounce');
        }
    </script>
</body>
</html>