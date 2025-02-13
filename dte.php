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
    $stmt = $pdo->prepare("SELECT names FROM employeenames WHERE id = 4");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $name = $row['names'];
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}

// Handle "Refuser" action
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM demandes WHERE id = ?");
    $stmt->execute([$delete_id]);

    // Reload the current page without redirection
    header("Content-Type: application/json");
    echo json_encode(["status" => "deleted"]);
    exit;
}

// Fetch data from 'transportfee' table where 'poste' matches the specified values
$query = $pdo->prepare("
    SELECT id, nom_et_prenom, poste, lieu_depart, adresse_destination, motif_deplacement, 
           aller, aller_retour, taxi, location_voiture, montant, created_at
    FROM transportfee 
    WHERE poste IN ('Chef de projet', 'Directeur technique et exploitation', 
                     'Maintenancier des scanners', 'Suivi électronique', 'Informaticien')
");
$query->execute();
$transportFees = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch data from 'fiche_de_demande_fonds' table where 'poste_du_demandeur' matches specified values
$query = $pdo->prepare("
    SELECT id, nom_du_demandeur, poste_du_demandeur, motif, montant_demande, montant_en_lettre, created_at
    FROM fiche_de_demande_fonds
    WHERE poste_du_demandeur IN ('Chef de projet', 'Directeur technique et exploitation', 
                                 'Maintenancier des scanners', 'Suivi électronique', 'Informaticien')
");
$query->execute();
$ficheDemandeFondsData = $query->fetchAll(PDO::FETCH_ASSOC);



// Handle "Approuver" action
if (isset($_GET['approve_id'])) {
    $approve_id = $_GET['approve_id'];
    $stmt = $pdo->prepare("UPDATE demandes SET status = 'déjà approuvé' WHERE id = ?");
    $stmt->execute([$approve_id]);

    // Reload the current page without redirection
    header("Content-Type: application/json");
    echo json_encode(["status" => "approved"]);
    exit;
}

try {
    // Fetch specified records from the 'demandes' table
    $stmt = $pdo->query("SELECT id, designation, quantite_demandee, observation, status, forwarded_to, requester_role FROM demandes");
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/x-icon" href="images/apple-touch-icon.png">
    <title>Gestion de Demande</title>
    <style>
        
body {
    background-color: whitesmoke; /* Dark background for the whole page */
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
    width: 114px;
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
    width: 88%;
    margin-top: 30px;
    border-radius: 10px; /* Rounded corners */
    overflow: hidden; /* To clip the corners */
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); /* Shadow effect */
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
    padding: 16px; /* Increased padding for header */
    font-size: 1.7em; /* Increased font size for header */
    text-align: left; /* Align text to left */
}

td {
    padding: 16px; /* Increased padding for cells */
    border: 1px solid #ddd; /* Light border for cells */
    transition: background-color 0.3s; /* Transition for hover effect */
    font-size: 1.5em;
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

    <script>
        // Confirm delete action
        function confirmDelete() {
            return confirm('Êtes-vous sûr de vouloir refuser cette demande ?');
        }

        // Handle delete and approve actions
        function handleAction(action, id) {
            if (action === 'delete' && !confirmDelete()) return;

            const xhr = new XMLHttpRequest();
            xhr.open('GET', `index.php?${action}_id=${id}`, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'deleted') {
                        // Show confirmation message
                        const confirmationMessage = document.getElementById('confirmation-message');
                        confirmationMessage.style.display = 'block'; // Show confirmation message
                        confirmationMessage.innerText = 'Demande refusée avec succès.';
                        document.getElementById(`row-${id}`).remove(); // Remove the row from the table
                    } else if (response.status === 'approved') {
                        // Update status display
                        document.getElementById(`status-${id}`).innerText = 'déjà approuvé';
                        document.getElementById(`status-${id}`).classList.add('status-approved');
                    }
                }
            };
            xhr.send();
        }
    </script>
    <script>
        function exportTableToExcel(tableID, filename = '') {
            let downloadLink;
            const dataType = 'application/vnd.ms-excel';
            const tableSelect = document.getElementById(tableID);
            const tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

            filename = filename ? filename + '.xls' : 'excel_data.xls';

            downloadLink = document.createElement("a");
            document.body.appendChild(downloadLink);

            if (navigator.msSaveOrOpenBlob) {
                const blob = new Blob(['\ufeff', tableHTML], { type: dataType });
                navigator.msSaveOrOpenBlob(blob, filename);
            } else {
                downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
                downloadLink.download = filename;
                downloadLink.click();
            }
        }
    </script>



</head>
<body>
    <di class="dashboard-container">
    <?php
// Include your database connection
require 'database.php';

// Query to get the count of demandes with status "en attente"
$stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM demandes WHERE status = 'en attente'");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$notificationCount = $result['count']; // Get the count of notifications
?>

<aside class="sidebar">
    <a href="dte.php">
        <div class="logo-container">
            <img src="logo/logo_kontel1.png" alt="Logo Kontel" class="logo bounce" />
        </div>
    </a>
    <h2>Directeur Technique et Exploitation</h2>
    <ul>
        <li onmouseover="addBounceEffect(this)" onmouseout="removeBounceEffect(this)" onclick="navigateTo('dashboard')" class="nav-link" data-tab="dashboard">
            <i class="fas fa-home"></i> Page d'accueil
        </li>
        <li onmouseover="addBounceEffect(this)" onmouseout="removeBounceEffect(this)" onclick="navigateTo('requests')" class="nav-link" data-tab="requests">
            <i class="fas fa-box"></i> Matériel demandé
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
        <p></p>
        <div class="clickable-boxes">
        
        <div onclick="navigateTo('form')" class="box">
            <i class="fas fa-file-alt"></i> Formulaires de Demandes
        </div>
        <div onclick="navigateTo('requests')" class="box">
            <i class="fas fa-box"></i> Matériel demandé
        </div>
        <div onclick="navigateTo('status')" class="box">
            <i class="bi bi-hourglass-bottom"></i> Status du demande
        </div>
        
        <div onclick="navigateTo('gestion')" class="box">
            <i class="fas fa-users"></i> Gestion des utilisateurs
        </div>
        <div onclick="navigateTo('parametres')" class="box">
            <i class="fas fa-cogs"></i> Paramètres
        </div>
    </div>
</div>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Table to Excel</title>
    <script>
        function exportTableToExcel(tableID, filename = '') {
            let downloadLink;
            const dataType = 'application/vnd.ms-excel';
            const tableSelect = document.getElementById(tableID);
            const tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

            filename = filename ? filename + '.xls' : 'excel_data.xls';

            downloadLink = document.createElement("a");
            document.body.appendChild(downloadLink);

            if (navigator.msSaveOrOpenBlob) {
                const blob = new Blob(['\ufeff', tableHTML], { type: dataType });
                navigator.msSaveOrOpenBlob(blob, filename);
            } else {
                downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
                downloadLink.download = filename;
                downloadLink.click();
            }
        }
    </script>


    <style>
        /* Basic styling for table */
        .content-section {
            padding: 10px;
        }
        
        table {
            width: 90%;
            margin-left: 50px;
            
            
        }
        th, td {
            padding: 16px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 20px;
        }

        /* Style for the export button */
        .export-button {
            padding: 12px 15px;
            background-color: darkgreen; /* Green color */
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-bottom: -5px;
            margin-left: 1300px;
            width: 208px;
        }
        .export-button:hover {
            background-color: #218838;
        }

        

    </style>
</head>






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





<?php
// Database connection
require 'database.php'; // Ensure this file contains the PDO setup

// Fetch data for 'Matériel demandé' where requester_role is 'Chef de Projet' or 'DTE'
$query = $pdo->prepare("
    SELECT id, nom_et_prenom, date_demande, designation, quantite_demandee, observation, requester_role 
    FROM demandes 
    WHERE requester_role IN (:role1, :role2)
");
$role1 = 'Chef de Projet';
$role2 = 'DTE';
$query->bindParam(':role1', $role1);
$query->bindParam(':role2', $role2);
$query->execute();
$demandes = $query->fetchAll(PDO::FETCH_ASSOC);

// Placeholder data for the other two sections
$fraisDeDeplacement = []; // Replace with actual query if needed
$ficheDemandeFonds = []; // Replace with actual query if needed
?>

<div id="requests-content" class="content-section">
    <h1>Matériel demandé</h1>

    <!-- Subtitle 1 -->
    <h2 onclick="toggleSection('materielSection', 'iconMateriel')" style="cursor: pointer; color: black; text-align: left; border-bottom: 1px solid transparent; padding-bottom: 3px; margin-bottom: 2px;">
        <span id="iconMateriel" class="bi bi-clipboard-check-fill" style="margin-right: 10px;"></span>
        Pour le Materiel de Consommation
        <span id="toggleIconMateriel" style="float: right;">+</span>
    </h2>
    <div id="materielSection" style="display: none;">
        <button onclick="exportTableToExcel('demandeTable', 'demande_data')" class="export-button">
            <i class="bi bi-file-excel"></i> Exporter vers Excel
        </button>
        <div class="scrollable-table">
            <table id="demandeTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom et Prénom</th>
                        <th>Rôle du Demandeur</th>
                        <th>Désignation</th>
                        <th>Quantité Demandée</th>
                        <th>Observation</th>
                        <th>Date de demande</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($demandes)): ?>
                        <?php foreach ($demandes as $demande): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($demande['id']); ?></td>
                                <td><?php echo htmlspecialchars($demande['nom_et_prenom']); ?></td>
                                <td><?php echo htmlspecialchars($demande['requester_role']); ?></td>
                                <td><?php echo htmlspecialchars($demande['designation']); ?></td>
                                <td><?php echo htmlspecialchars($demande['quantite_demandee']); ?></td>
                                <td><?php echo htmlspecialchars($demande['observation']); ?></td>
                                <td><?php echo htmlspecialchars($demande['date_demande']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">Aucune demande trouvée.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Divider -->
    <hr style="border: none; border-top: 1px solid #006400; margin: 30px 0; margin-left: 60px;">
    
    
    

<!-- Subtitle 2 -->
<h2 onclick="toggleSection('fraisDeDeplacementSection', 'iconFrais')" 
    style="cursor: pointer; color: black; text-align: left; border-bottom: 1px solid transparent; padding-bottom: 3px; margin-bottom: 2px;">
    <span id="iconFrais" class="bi bi-currency-dollar" style="margin-right: 10px;"></span>
    Pour Les Frais de Déplacement
    <span id="toggleIconFrais" style="float: right;">+</span>
</h2>

<div id="fraisDeDeplacementSection" style="display: none;">
    <!-- Table to display fetched transport fee data -->
    <table id="fraisDeDeplacementTable" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Départ</th>
                <th>Destination</th>
                <th>Motif</th>
                <th>Aller</th>
                <th>Aller Retour</th>
                <th>Taxi</th>
                <th>Montant</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($transportFees)): ?>
                <?php foreach ($transportFees as $fee): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fee['id']); ?></td>
                        <td><?php echo htmlspecialchars($fee['nom_et_prenom']); ?></td>
                        <td><?php echo htmlspecialchars($fee['lieu_depart']); ?></td>
                        <td><?php echo htmlspecialchars($fee['adresse_destination']); ?></td>
                        <td><?php echo htmlspecialchars($fee['motif_deplacement']); ?></td>
                        <td><?php echo htmlspecialchars($fee['aller']); ?></td>
                        <td><?php echo htmlspecialchars($fee['aller_retour']); ?></td>
                        <td><?php echo htmlspecialchars($fee['taxi']); ?></td>
                        <td><?php echo htmlspecialchars($fee['montant']); ?></td>
                        <td><?php echo htmlspecialchars($fee['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="12">Aucune donnée trouvée.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Divider -->
<hr style="border: none; border-top: 1px solid #006400; margin: 30px 0; margin-left: 60px;">

<!-- JavaScript for toggle functionality -->
<script>
    function toggleSection(sectionId, iconId) {
        const section = document.getElementById(sectionId);
        const toggleIcon = document.getElementById("toggleIcon" + iconId.charAt(0).toUpperCase() + iconId.slice(1));

        if (section.style.display === 'none') {
            section.style.display = 'block';
            toggleIcon.innerHTML = 'x'; // Change icon to 'x' when section is visible
        } else {
            section.style.display = 'none';
            toggleIcon.innerHTML = '+'; // Change icon back to '+' when section is hidden
        }
    }
</script>

    
    

    
    <!-- Subtitle 3 -->
<h2 onclick="toggleSection('ficheDemandeFondsSection', 'iconFiche')" 
    style="cursor: pointer; color: black; text-align: left; border-bottom: 1px solid transparent; padding-bottom: 3px; margin-bottom: 2px;">
    <span id="iconFiche" class="bi bi-file-earmark-text-fill" style="margin-right: 10px;"></span>
    Pour Fiche de Demande des Fonds
    <span id="toggleIconFiche" style="float: right;">+</span>
</h2>

<div id="ficheDemandeFondsSection" style="display: none;">
    <!-- Table to display fetched data -->
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Poste du demandeur</th>
                <th>Motif</th>
                <th>Montant Demandé</th>
                <th>Montant en Lettre</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($ficheDemandeFondsData)): ?>
                <?php foreach ($ficheDemandeFondsData as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['nom_du_demandeur']); ?></td>
                        <td><?php echo htmlspecialchars($row['poste_du_demandeur']); ?></td>
                        <td><?php echo htmlspecialchars($row['motif']); ?></td>
                        <td><?php echo htmlspecialchars($row['montant_demande']); ?></td>
                        <td><?php echo htmlspecialchars($row['montant_en_lettre']); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Aucune donnée trouvée.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Divider -->
<hr style="border: none; border-top: 1px solid #006400; margin: 30px 0; margin-left: 60px;">

<!-- JavaScript for toggle functionality -->
<script>
    function toggleSection(sectionId, iconId) {
        const section = document.getElementById(sectionId);
        const toggleIcon = document.getElementById("toggleIcon" + iconId.charAt(0).toUpperCase() + iconId.slice(1));

        if (section.style.display === 'none') {
            section.style.display = 'block';
            toggleIcon.innerHTML = 'x'; // Change icon to 'x' when section is visible
        } else {
            section.style.display = 'none';
            toggleIcon.innerHTML = '+'; // Change icon back to '+' when section is hidden
        }
    }
</script>
</div>


<script>
    // Function to toggle visibility of sections and update the icon
    function toggleSection(sectionId, iconId) {
        const section = document.getElementById(sectionId);
        const toggleIcon = document.getElementById("toggleIcon" + iconId.charAt(0).toUpperCase() + iconId.slice(1));

        if (section.style.display === 'none') {
            section.style.display = 'block';
            toggleIcon.innerHTML = 'x'; // Change icon to 'x' when section is visible
        } else {
            section.style.display = 'none';
            toggleIcon.innerHTML = '+'; // Change icon back to '+' when section is hidden
        }
    }
</script>
</body>
</html>




<?php
// Require the database connection
require 'database.php';

// Fetch data from 'demandes' table with sorting based on requester_role
$query = $pdo->prepare("SELECT id, nom_et_prenom, designation, quantite_demandee, status, requester_role FROM demandes WHERE requester_role IN ('Chef de Projet', 'DTE') ORDER BY requester_role ASC");
$query->execute();
$demandes = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="status-content" class="content-section" style="overflow-y: auto; max-height: 90vh;">
    <h1>Status du demande</h1>

    <!-- Subtitle "Pour le Materiel de Consommation" -->
    <div class="section">
        <h2 class="toggle-subtitle" onclick="toggleContent('materiel')">
            <i class="bi bi-clipboard-check-fill"></i> Pour le Materiel de Consommation
            <span class="toggle-icon">+</span> <!-- + sign initially -->
        </h2>
        <div id="materiel" class="content" style="display: none;">
            <div class="confirmation-message" id="confirmation-message"></div>
            <div class="scrollable-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom et prénom</th>
                            <th>Rôle du Demandeur</th>
                            <th>Désignation</th>
                            <th>Quantité Demandée</th>
                            <th>Status</th>
                            <th>Agissez</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($demandes)): ?>
                            <?php foreach ($demandes as $demande): ?>
                                <tr id="row-<?php echo $demande['id']; ?>">
                                    <td><?php echo htmlspecialchars($demande['id']); ?></td>
                                    <td><?php echo htmlspecialchars($demande['nom_et_prenom']); ?></td>
                                    <td><?php echo htmlspecialchars($demande['requester_role']); ?></td>
                                    <td><?php echo htmlspecialchars($demande['designation']); ?></td>
                                    <td><?php echo htmlspecialchars($demande['quantite_demandee']); ?></td>
                                    <td id="status-<?php echo $demande['id']; ?>" 
                                              class="<?php 
                                              echo ($demande['status'] === 'déjà approuvé') 
                                        ? 'status-approved' 
                                        : (in_array($demande['status'], ['en cours de vérification (actuellement au DA&F)', 'en cours de vérification (actuellement au DG)']) 
                                        ? 'status-in-progress' 
                                        : ''); 
                                        ?>">
                                        <?php echo htmlspecialchars($demande['status']); ?>
                                    </td>
                                    <td>
                                        <?php if (in_array($demande['requester_role'], ['Chef de Projet', 'Informaticien'])): ?>
                                        <!-- Approve and Delete buttons for 'Chef de Service', 'Chef de Projet', and 'DTE' -->
                                           <button class="action-button approve-btn" onclick="handleAction('approve', <?php echo $demande['id']; ?>)">Transmettre</button>
                                           <button class="action-button delete-btn" onclick="handleAction('delete', <?php echo $demande['id']; ?>)">Refuser</button>
                                        <?php else: ?>
                                        <!-- Message for other roles -->
                                        <span>Pas autorisé.</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">Aucun statut de demande trouvé.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="divider"></div>
    </div>




    <div class="section">
    <h2 class="toggle-subtitle" onclick="toggleContent('frais')">
        <i class="bi bi-currency-dollar"></i> Pour Les Frais de Déplacement
        <span class="toggle-icon">+</span> <!-- + sign initially -->
    </h2>
    <div id="frais" class="content" style="display: none;">
        <div class="confirmation-message" id="confirmation-message"></div>
        <div class="scrollable-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom et prénom</th>
                        <th>Poste</th>
                        <th>Téléphone</th>
                        <th>Lieu de départ</th>
                        <th>Adresse destination</th>
                        <th>Motif de déplacement</th>
                        <th>Montant</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
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

                    // Fetch data from transportfee table
                    $sql = "SELECT * FROM transportfee WHERE poste IN ('Directeur technique et exploitation', 'Chef de projet', 'Maintenancier des scanners', 'Suivi électronique', 'Informaticien')";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0):
                        while ($row = $result->fetch_assoc()): ?>
                            <tr id="row-<?php echo $row['id']; ?>">
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['nom_et_prenom']); ?></td>
                                <td><?php echo htmlspecialchars($row['poste']); ?></td>
                                <td><?php echo htmlspecialchars($row['telephone']); ?></td>
                                <td><?php echo htmlspecialchars($row['lieu_depart']); ?></td>
                                <td><?php echo htmlspecialchars($row['adresse_destination']); ?></td>
                                <td><?php echo htmlspecialchars($row['motif_deplacement']); ?></td>
                                <td><?php echo htmlspecialchars($row['montant']); ?></td>
                                <td id="status-<?php echo $row['id']; ?>" class="<?php 
    echo (!empty($row['status']) && $row['status'] === 'déjà approuvé') ? 'status-approved' : 
         ((!empty($row['status']) && 
          ($row['status'] === 'en cours de vérification (actuellement au DA&F)' || 
           $row['status'] === 'en cours de vérification (actuellement au DG)')) ? 'status-in-progress' : '' );
?>">
    <?php echo htmlspecialchars($row['status'] ?? 'Non défini'); ?>
</td>

                                <td>
                                    <!-- Approve Button -->
                                    <button onclick="confirmAction('approve', <?php echo $row['id']; ?>)" class="action-button approve-btn">Transmettre</button>

                                    <!-- Reject Button -->
                                    <button onclick="confirmAction('delete', <?php echo $row['id']; ?>)" class="action-button delete-btn">Refuser</button>
                                </td>
                            </tr>
                        <?php endwhile;
                    else: ?>
                        <tr>
                            <td colspan="10">Aucune donnée trouvée.</td>
                        </tr>
                    <?php endif;

                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="divider"></div>
</div>

<script>
function confirmAction(action, id) {
    var message = (action === 'approve') 
        ? "Êtes-vous sûr de vouloir transmettre cette demande vers le Directeur Administratif et Financier pour la suivante vérification?" 
        : "Êtes-vous sûr de vouloir refuser cette demande ?";
    
    if (confirm(message)) {
        // Create form data to send via fetch
        var formData = new FormData();
        formData.append('id', id);
        formData.append('action', action);

        // Send data using fetch
        fetch('process_action1.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log(data); // Optional: log the response for debugging
            // Update status without reloading the page
            var statusElement = document.getElementById('status-' + id);
            if (action === 'approve') {
                statusElement.textContent = 'en cours de vérification (actuellement au DA&F)';
                statusElement.className = 'status-approved';
            } else if (action === 'delete') {
                statusElement.textContent = 'refusé';
                statusElement.className = 'status-rejected';
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
}
</script>

    
<!-- Subtitle "Pour Fiche de Demande des Fonds" -->
<div class="section">
    <h2 class="toggle-subtitle" onclick="toggleContent('fiche')">
        <i class="bi bi-file-earmark-text-fill"></i> Pour Fiche de Demande des Fonds
        <span class="toggle-icon">+</span> <!-- + sign initially -->
    </h2>
    <div id="fiche" class="content" style="display: none;">
        <div class="confirmation-message" id="confirmation-message"></div>
        <div class="scrollable-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Motif</th>
                        <th>Nom du Demandeur</th>
                        <th>Poste du Demandeur</th>
                        <th>Montant Demandé</th>
                        <th>Montant en Lettre</th>
                        <th>Date Créée</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
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

                    // Fetch data from fiche_de_demande_fonds table
                    $sql = "SELECT * FROM fiche_de_demande_fonds WHERE poste_du_demandeur IN ('Directeur technique et exploitation', 'Chef de projet', 'Maintenancier des scanners', 'Suivi électronique', 'Informaticien')";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0):
                        while ($row = $result->fetch_assoc()): ?>
                            <tr id="row-<?php echo $row['id']; ?>">
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['motif']); ?></td>
                                <td><?php echo htmlspecialchars($row['nom_du_demandeur']); ?></td>
                                <td><?php echo htmlspecialchars($row['poste_du_demandeur']); ?></td>
                                <td><?php echo htmlspecialchars($row['montant_demande']); ?></td>
                                <td><?php echo htmlspecialchars($row['montant_en_lettre']); ?></td>
                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                <td id="status-<?php echo $row['id']; ?>" class="<?php 
    echo (!empty($row['status']) && $row['status'] === 'déjà approuvé') ? 'status-approved' : 
         ((!empty($row['status']) && $row['status'] === 'en cours de vérification') ? 'status-in-progress' : 
         ((!empty($row['status']) && $row['status'] === 'en attente') ? 'status-pending' : '' ));
?>">
    <?php echo htmlspecialchars($row['status'] ?? 'Non défini'); ?>
</td>

                                <td>
                                    <!-- Approve Button -->
                                    <button onclick="confirmActionFiche('approve', <?php echo $row['id']; ?>)" class="action-button approve-btn">Transmettre</button>

                                    <!-- Reject Button -->
                                    <button onclick="confirmActionFiche('delete', <?php echo $row['id']; ?>)" class="action-button delete-btn">Refuser</button>
                                </td>
                            </tr>
                        <?php endwhile;
                    else: ?>
                        <tr>
                            <td colspan="9">Aucune donnée trouvée.</td>
                        </tr>
                    <?php endif;

                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="divider"></div>
</div>

<script>
function confirmActionFiche(action, id) {
    var message = (action === 'approve') 
        ? "Êtes-vous sûr de vouloir transmettre cette demande vers le Directeur Administratif et Financier pour la suivante vérification?" 
        : "Êtes-vous sûr de vouloir refuser cette demande ?";
    
    if (confirm(message)) {
        // Create form data to send via fetch
        var formData = new FormData();
        formData.append('id', id);
        formData.append('action', action);

        // Send data using fetch
        fetch('FichedeFonds/process_fiche_action.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log(data); // Optional: log the response for debugging
            // Update status or remove row
            if (action === 'approve') {
                var statusElement = document.getElementById('status-' + id);
                statusElement.textContent = 'en cours de vérification (actuellement au DA&F)';
                statusElement.className = 'status-in-progress';
            } else if (action === 'delete') {
                var rowElement = document.getElementById('row-' + id);
                rowElement.remove();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
}
</script>
</div>


<style>
    /* Styles for status colors */
    .status-pending {
    color: black; /* Pending status in black */
    }
    .status-approved {
    color: green;
    background-color: #e6ffe6;
    border-radius: 10px;
    padding: 5px;
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
    #status-content h2 {
        color: black;
        margin-left: -20px;
    }

</style>


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
        if (confirm('Êtes-vous sûr de vouloir transmettre cette demande vers le Directeur Administratif et Financier pour la suivante vérification?')) {
            // Update status in the database
            fetch('update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id, status: 'en cours de vérification (actuellement au DA&F)' }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById(`status-${id}`).innerText = 'en cours de vérification (actuellement au DA&F)';
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


<div id="parametres-content" class="content-section">
    <h1>Paramètres</h1>
    
    <!-- Profile Picture Section -->
    <div class="profile-picture-section">
        <?php
        // Fetch profile picture and user details from database
        $userId = 4; // User ID
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
                    <i class="bi bi-briefcase-fill"></i> <strong>Poste:</strong> <?php echo $row['poste']; ?>
                </div>
                <div class="user-info-column">
                    <i class="bi bi-building-fill"></i> <strong>Département:</strong> <?php echo $row['Departement']; ?>
                </div>
            </div>
            <hr>
            <div class="user-info-row">
                <div class="user-info-column">
                    <i class="bi bi-telephone-fill"></i> <strong>Téléphone:</strong> <?php echo $row['telephone']; ?>
                </div>
                <div class="user-info-column">
                    <i class="bi bi-envelope-fill"></i> <strong>Email:</strong> <?php echo $row['email']; ?>
                </div>
                <div class="user-info-column">
                    <i class="bi bi-gender-ambiguous"></i> <strong>Genre:</strong> <?php echo $row['Genre']; ?>
                </div>
                <div class="user-info-column">
                    <i class="bi bi-calendar-event-fill"></i> <strong>Naissance:</strong> <?php echo $row['naissance']; ?>
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
$stmt = $pdo->prepare("SELECT * FROM poste WHERE id = 4");
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
        <h2>Modifier les informations personnelles</h2>
        <form id="profile-form">
            <div class="form-container">
        <!-- Left Side Form Fields -->
        <div class="form-left">
            <div class="form-group">
                <label for="nom">
                    <i class="bi bi-person-fill"></i> Nom:
                </label>
                <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($userData['Nom']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="prenom">
                    <i class="bi bi-person-fill"></i> Prenom:
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
                    <i class="bi bi-envelope-fill"></i> Email:
                </label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
            </div>
        </div>

        <!-- Right Side Form Fields -->
        <div class="form-right">
            <div class="form-group">
                <label for="telephone">
                    <i class="bi bi-phone-fill"></i> Téléphone:
                </label>
                <input type="text" id="telephone" name="telephone" value="<?php echo htmlspecialchars($userData['telephone']); ?>" required>
            </div>

            <div class="form-group">
                <label for="naissance">
                    <i class="bi bi-calendar-fill"></i> Date de Naissance:
                </label>
                <input type="date" id="naissance" name="naissance" value="<?php echo htmlspecialchars($userData['naissance']); ?>" required>
            </div>

            <div class="form-group">
                <label for="poste">
                    <i class="bi bi-briefcase-fill"></i> Poste:
                </label>
                <input type="text" id="poste" name="poste" value="<?php echo htmlspecialchars($userData['poste']); ?>" required>
            </div>

            <div class="form-group">
                <label for="departement">
                    <i class="bi bi-building-fill"></i> Département:
                </label>
                <input type="text" id="departement" name="departement" value="<?php echo htmlspecialchars($userData['Departement']); ?>" required>
            </div>
        </div>
    </div>

    <!-- Hidden input for ID -->
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($userData['id']); ?>">

    <div class="button-group">
        <button type="submit" class="update-btn">
            <i class="bi bi-save-fill"></i>Mettre à jour
        </button>
        <button type="button" class="close-btn" onclick="closePopup()">X</button>
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
        width: 990px;
        text-align: left;
        font-size: 18px;
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
        <h2>Changer le mot de passe</h2>
        <form id="change-password-form" action="update-user.php" method="POST">
            <div class="form-group">
                <label for="current-password">
                    <i class="bi bi-lock"></i> Mot de passe actuel:
                </label>
                <input type="password" id="current-password" name="current-password" required>
            </div>
            <div class="form-group">
                <label for="password">
                    <i class="bi bi-lock-alt"></i> Nouveau mot de passe:
                </label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm-password">
                    <i class="bi bi-lock-open"></i> Confirmer le mot de passe:
                </label>
                <input type="password" id="confirm-password" name="confirm-password" required>
            </div>
            <div class="button-group">
                <button type="submit" class="update-btn">Mettre à jour</button>
                <button type="button" class="close-btn" onclick="closePopup()">X</button>
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

    fetch('update-user.php', {
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


<div id="form-content" class="content-section">
    <h1>Formulaires de Demande</h1>
    <p></p>

    <div class="icon-grid">
    <a href="consommation/form4.html" class="icon-item">
        <div class="icon-box">
            <i class="bi bi-clipboard-check-fill"></i>
            <p>Materiel de Consomation</p>
        </div>
    </a>
    <a href="transportfee/deplacement.php" class="icon-item">
        <div class="icon-box">
            <i class="bi bi-ev-front-fill"></i>
            <p>Frais de Deplacement</p>
        </div>
    </a>
    <a href="fichedefonds/fonds.php" class="icon-item">
        <div class="icon-box">
            <i class="bi bi-file-earmark-text-fill"></i>
            <p>Fiche de Demande des Fonds</p>
        </div>
    </a>
</div>
</div>

<div id="gestion-content" class="content-section">
    <h1>Gestion des employé(e)s</h1>
    <!-- Button to Open the Modal -->
    <button id="addEmployeeBtn">Ajout un employé</button>

    <!-- Modal (Popup Form) -->
    <div id="employeeModal" class="modal">
        <div class="modal-content">
            <!-- New Close Button in the middle -->
            <button type="button" class="close-middle-btn">X</button>

            <h2><i class="fas fa-user-plus"></i> Ajouter un employé </h2>

            <form id="employeeForm" action="Employee/add-employee.php" method="POST" onsubmit="return confirmSubmission()">
                <div class="left-column">
                    <label for="nom"><i class="bi bi-person-fill"></i> Nom:</label>
                    <input type="text" id="nom" name="nom" required>

                    <label for="prenom"><i class="bi bi-person-fill"></i> Prenom:</label>
                    <input type="text" id="prenom" name="prenom" required>

                    <label for="nomPrenom"><i class="bi bi-person-fill"></i> Nom Complet:</label>
                    <input type="text" id="nomPrenom" name="nomPrenom" required>

                    <label for="email"><i class="bi bi-envelope"></i> E-mail:</label>
                    <input type="email" id="email" name="email" required>

                    <label for="telephone"><i class="bi bi-telephone"></i> Phone:</label>
                    <input type="tel" id="telephone" name="telephone" required>
                </div>

                <div class="right-column">
                    <label for="genre"><i class="bi bi-gender-male-female"></i> Gender:</label>
                    <input type="text" id="genre" name="genre" required>

                    <label for="naissance"><i class="bi bi-calendar"></i> Birth:</label>
                    <input type="date" id="naissance" name="naissance" required>

                    <label for="poste"><i class="bi bi-briefcase"></i> Poste:</label>
                    <select id="poste" name="poste" required>
                        <option value="" disabled selected>Select Poste</option>
                        <option value="Chef de projet">Chef de projet</option>
                        <option value="Informaticien">Informaticien</option>
                        <option value="Maintenancier des scanners">Maintenancier des scanners</option>
                        <option value="Suivi électronique">Suivi électronique</option>
                        <option value="Caissière">Caissière</option>
                        <option value="Comptable">Comptable</option>
                        <option value="Technicien de surface">Technicien de surface</option>
                        <option value="Chef de Service">Chef de Service</option>
                    </select>

                    <label for="departement"><i class="bi bi-house-door"></i> Department:</label>
                    <select id="departement" name="departement" required>
                        <option value="" disabled selected>Select Department</option>
                        <option value="Maintenance des scannaires">Maintenance des scannaires</option>
                        <option value="Suivi électronique des cargos">Suivi électronique des cargos</option>
                        <option value="Génie Logiciel">Génie Logiciel</option>
                    </select>

                    <label for="password"><i class="bi bi-lock"></i> Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-footer">
                    <button type="submit" class="submit-btn">
                        <i class="bi bi-check-lg"></i> Soumettre
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Function to confirm submission
        function confirmSubmission() {
            // Confirmation message in French
            return confirm("Êtes-vous sûr de vouloir ajouter l'employé ?");
        }

        // Function to open the modal
        function openModal() {
            document.getElementById("employeeModal").style.display = "block";
        }

        // Function to close the modal
        function closeModal() {
            document.getElementById("employeeModal").style.display = "none";
        }

        // Open the modal when the "Add Employee" button is clicked
        document.getElementById("addEmployeeBtn").addEventListener("click", openModal);

        // Close the modal when the "Close" button in the middle is clicked
        document.querySelector(".close-middle-btn").addEventListener("click", closeModal);

        // Close modal when clicking outside the modal content
        window.addEventListener("click", function(event) {
            var modal = document.getElementById("employeeModal");
            if (event.target === modal) {
                closeModal();
            }
        });
    </script>

<style>
/* Style for the Close Button in the middle of the modal */
.close-middle-btn {
    background-color: red;  /* Red background */
    color: white;  /* White text */
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    font-size: 23px;
    cursor: pointer;
    position: absolute;
    top: 10px;
    left: 95%;
    transform: translateX(-50%);
    z-index: 10;
    text-align: center;
}

.close-middle-btn:hover {
    background-color: #c0392b; /* Slightly darker red on hover */
}

</style>




<?php
include 'database.php'; // Include your database connection

// Handle Delete Request
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id']; // Ensure it's an integer for safety

    try {
        // Prepare the DELETE query
        $stmt = $pdo->prepare("DELETE FROM poste WHERE id = :id");
        $stmt->bindParam(':id', $deleteId, PDO::PARAM_INT);

        // Execute the query and check for success
        if ($stmt->execute()) {
            echo "<script>alert('Employee deleted successfully.'); window.location.href = 'your-page-name.php';</script>";
        } else {
            echo "<script>alert('Failed to delete employee.');</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}
?>


<!-- Responsive Scrollable Table -->
<div class="table-container">
    <table class="employee-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prenom</th>
                <th>Genre</th>
                <th>Email</th>
                <th>Telephone</th>
                <th>Poste</th>
                <th>Departement</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            try {
                // Fetch employees from the database
                $query = "SELECT * FROM poste WHERE poste IN ('Directeur technique et exploitation', 'Chef de projet', 'Maintenancier des scanners', 'Suivi électronique', 'Informaticien')";
                $employees = $pdo->query($query)->fetchAll();
            } catch (Exception $e) {
                echo "<tr><td colspan='10'>Error fetching employees: " . $e->getMessage() . "</td></tr>";
                $employees = [];
            }

            // Display employee data
            if (!empty($employees)) {
                foreach ($employees as $employee) {
                    echo "<tr>
                        <td>{$employee['id']}</td>
                        <td>{$employee['Nom']}</td>
                        <td>{$employee['Prenom']}</td>
                        <td>{$employee['Genre']}</td>
                        <td>{$employee['email']}</td>
                        <td>{$employee['telephone']}</td>
                        <td>{$employee['poste']}</td>
                        <td>{$employee['Departement']}</td>
                        <td>
                            <button class='view-btn' onclick='openEmployeeDetailsModal({$employee['id']})'>View</button>

                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='10'>No employees found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>


<!-- Employee Details Modal -->
<div id="employeeDetailsModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2>Détails sur l'employé</h2>
        <form>
            <label for="employee-id">ID:</label>
            <input type="text" id="employee-id" readonly>
            
            <label for="employee-nom">Nom:</label>
            <input type="text" id="employee-nom" readonly>
            
            <label for="employee-prenom">Prenom:</label>
            <input type="text" id="employee-prenom" readonly>
            
            <label for="employee-genre">Genre:</label>
            <input type="text" id="employee-genre" readonly>

            <label for="employee-email">Email:</label>
            <input type="email" id="employee-email" readonly>
            
            <label for="employee-telephone">Telephone:</label>
            <input type="tel" id="employee-telephone" readonly>
            
            <label for="employee-poste">Poste:</label>
            <input type="text" id="employee-poste" readonly>
            
            <label for="employee-departement">Departement:</label>
            <input type="text" id="employee-departement" readonly>
        </form>
    </div>
</div>


<!-- Modal JavaScript -->
<script>
    // Function to open the modal and populate with employee details
    function openEmployeeDetailsModal(employeeId) {
        console.log("Fetching data for employee ID:", employeeId);
        
        // Fetch employee data via AJAX
        fetch('Employee/get-employee-details.php?id=' + employeeId)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    document.getElementById('employee-id').value = data.id;
                    document.getElementById('employee-nom').value = data.Nom;
                    document.getElementById('employee-prenom').value = data.Prenom;
                    document.getElementById('employee-genre').value = data.Genre;
                    document.getElementById('employee-email').value = data.email;
                    document.getElementById('employee-telephone').value = data.telephone;
                    document.getElementById('employee-poste').value = data.poste;
                    document.getElementById('employee-departement').value = data.Departement;
                    document.getElementById('employeeDetailsModal').style.display = 'block';
                } else {
                    alert("No data found for this employee.");
                }
            })
            .catch(error => {
                console.error('Error fetching employee data:', error);
            });
    }

    // Function to close the modal
    function closeModal() {
        document.getElementById('employeeDetailsModal').style.display = 'none';
    }

    // Close the modal when clicking outside of it
    window.onclick = function(event) {
        if (event.target === document.getElementById('employeeDetailsModal')) {
            closeModal();
        }
    }
</script>

</main>
</div>


    <script>
        function navigateTo(page) {
            if (page === 'form4.html') {
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
        if (confirm('Êtes-vous sûr de vouloir transmettre cette demande vers le Directeur Administratif et Financier pour la suivante vérification?')) {
            // Update status in the database
            fetch('update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id, status: 'en cours de vérification (actuellement au DA&F)' }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById(`status-${id}`).innerText = 'en cours de vérification (actuellement au DG)';
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

</body>
</html>