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
$name = "Utilisateur"; // Default name if query fails

// Fetch the name from the 'employeenames' table
try {
    $stmt = $pdo->prepare("SELECT names FROM employeenames WHERE id = 1");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $name = $row['names'];
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}

// Fetching all data for each section
$queryMateriel = $pdo->query("SELECT * FROM demandes");
$materielDemandes = $queryMateriel->fetchAll(PDO::FETCH_ASSOC);

$queryTransportFee = $pdo->query("SELECT id, nom_et_prenom, poste, lieu_depart, adresse_destination, motif_deplacement, aller, aller_retour, taxi, transport_commun, location_voiture, montant, created_at FROM transportfee");
$transportFees = $queryTransportFee->fetchAll(PDO::FETCH_ASSOC);

$queryDecaissement = $pdo->query("SELECT * FROM demandes");
$decaissement = $queryDecaissement->fetchAll(PDO::FETCH_ASSOC);

$queryAutorisationPayement = $pdo->query("SELECT * FROM demandes");
$autorisationPayement = $queryAutorisationPayement->fetchAll(PDO::FETCH_ASSOC);

$queryAlimentationCaisse = $pdo->query("SELECT * FROM demandes");
$alimentationCaisse = $queryAlimentationCaisse->fetchAll(PDO::FETCH_ASSOC);


// Fetch the user's information based on user ID or session data
require 'database.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT email, password, language_preference, role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
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
}
.delete-btn {
    background-color: #f44336; /* Red */
}
.status-approved {
    color: darkgreengreen; /* Yellow for déjà approuvé */
}
.scrollable-table {
    overflow-x: auto; /* Enable horizontal scrolling */
}
.confirmation-message {
    display: none; /* Hidden by default */
    color: red; /* Red color for confirmation message */
}

table {
    max-width: 98%;
    width: 50%;
    margin-top: 10px;
    overflow: hidden; /* To clip the corners */
    animation: fadeIn 2s ease; /* Animation for fade in */
    margin-left: 25px;
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
    padding: 10px; /* Increased padding for header */
    font-size: 1.3em; /* Increased font size for header */
    text-align: left; /* Align text to left */
}

td {
    padding: 5px; /* Increased padding for cells */
    border: 1px solid #ddd; /* Light border for cells */
    transition: background-color 0.3s; /* Transition for hover effect */
    font-size: 1.1em;
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
    max-height: 900px; /* Set a max height for the table container */
    overflow-y: auto; /* Enable vertical scrolling */
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
    <div class="dashboard-container">
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
    <a href="index.php">
        <div class="logo-container">
            <img src="logo/logo_kontel1.png" alt="Logo Kontel" class="logo bounce" />
        </div>
    </a>

    <h2>Directeur Général</h2>
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
        <li onmouseover="addBounceEffect(this)" onmouseout="removeBounceEffect(this)" onclick="navigateTo('statistics')" class="nav-link" data-tab="statistics">
            <i class="fas fa-chart-bar"></i> Statistiques
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
        <div onclick="navigateTo('requests')" class="box">
            <i class="fas fa-box"></i> Matériel demandé
        </div>

        <div onclick="navigateTo('status')" class="box">
            <i class="bi bi-hourglass-bottom"></i> Status du demande
        </div>

        <div onclick="navigateTo('statistics')" class="box">
            <i class="fas fa-chart-bar"></i> Statistiques
        </div>
        
        <div onclick="navigateTo('gestion')" class="box">
            <i class="fas fa-users"></i> Gestion des utilisateurs
        </div>

        <div onclick="navigateTo('parametres')" class="box">
            <i class="fas fa-cogs"></i> Paramètres
        </div>
    </div>
</div>

    <style>
        /* Basic styling for table */
        .content-section {
            padding: 5px;
        }
        
        table {
            width: 96.4%;
            margin-bottom: 35px;
            
            
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 21px;
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

        #requests-content h2 {
            color: black;
            font-size: 24px;
        }

    </style>
</head>
<body>



<div id="requests-content" class="content-section" style="overflow-y: auto; max-height: 90vh;">
    <h1>Consultation du Matériel Demandé :</h1>

    <!-- Section: Pour le Materiel de Consommation -->
    <h2 onclick="toggleSection(this)" style="cursor: pointer;">
        <i class="bi bi-clipboard-check"></i> Pour le Materiel de Consommation 
        <span style="float: center;"><i class="bi bi-plus"></i></span>
    </h2>

    <div class="table-wrapper" style="display: none;">
        <button class="export-btn" onclick="exportTableToExcel('materiel-table', 'MaterielConsommation')">
            <i class="bi bi-clipboard-check"></i> Exporter vers Excel
        </button>

        <table id="materiel-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom complet</th>
                    <th>Rôle</th>
                    <th>Désignation</th>
                    <th>Quantité Demandée</th>
                    <th>Observation</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($materielDemandes as $demande): ?>
                <tr>
                    <td><?= htmlspecialchars($demande['id']); ?></td>
                    <td><?= htmlspecialchars($demande['nom_et_prenom']); ?></td>
                    <td><?= htmlspecialchars($demande['requester_role']); ?></td>
                    <td><?= htmlspecialchars($demande['designation']); ?></td>
                    <td><?= htmlspecialchars($demande['quantite_demandee']); ?></td>
                    <td><?= htmlspecialchars($demande['observation']); ?></td>
                    <td><?= htmlspecialchars($demande['date_demande']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="divider"></div>

    <!-- Section: Pour Les Frais de Deplacement -->
    <h2 onclick="toggleSection(this)" style="cursor: pointer;">
        <i class="bi bi-currency-dollar"> </i> Pour Les Frais de Deplacement 
        <span style="float: center;"><i class="bi bi-plus"></i></span>
    </h2>

    <div class="table-wrapper" style="display: none;">
        <button class="export-btn" onclick="exportTableToExcel('materiel-table', 'MaterielConsommation')">
            <i class="bi bi-file-earmark-arrow-down"></i> Exporter vers Excel
        </button>
        <table id="transportfee-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Poste</th>
                    <th>Départ</th>
                    <th>Destination</th>
                    <th>Motif</th>
                    <th>Aller</th>
                    <th>Aller-Retour</th>
                    <th>Taxi</th>
                    <th>Location Voiture</th>
                    <th>Montant</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transportFees as $transportFee): ?>
                <tr>
                    <td><?= htmlspecialchars($transportFee['id']); ?></td>
                    <td><?= htmlspecialchars($transportFee['nom_et_prenom']); ?></td>
                    <td><?= htmlspecialchars($transportFee['poste']); ?></td>
                    <td><?= htmlspecialchars($transportFee['lieu_depart']); ?></td>
                    <td><?= htmlspecialchars($transportFee['adresse_destination']); ?></td>
                    <td><?= htmlspecialchars($transportFee['motif_deplacement']); ?></td>
                    <td><?= htmlspecialchars($transportFee['aller']); ?></td>
                    <td><?= htmlspecialchars($transportFee['aller_retour']); ?></td>
                    <td><?= htmlspecialchars($transportFee['taxi']); ?></td>
                    <td><?= htmlspecialchars($transportFee['location_voiture']); ?></td>
                    <td><?= htmlspecialchars($transportFee['montant']); ?></td>
                    <td><?= htmlspecialchars($transportFee['created_at']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="divider"></div>

    <!-- Section: Pour Fiche de Demande des Fonds -->
    <h2 onclick="toggleSection(this)" style="cursor: pointer;">
        <i class="bi bi-file-earmark-text"></i> Pour Fiche de Demande des Fonds 
        <span style="float: center;"><i class="bi bi-plus"></i></span>
    </h2>

    <div class="table-wrapper" style="display: none;">
        <button class="export-btn" onclick="exportTableToExcel('materiel-table', 'MaterielConsommation')">
            <i class="bi bi-file-earmark-arrow-down"></i> Exporter vers Excel
        </button>
        <table id="funds-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom complet</th>
                    <th>Poste du demandeur</th>
                    <th>Motif</th>
                    <th>Montant Demandé</th>
                    <th>Montant en Lettre</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include('database.php');

                try {
                    $stmt = $pdo->query("SELECT id, motif, nom_du_demandeur, poste_du_demandeur, montant_demande, montant_en_lettre, created_at FROM fiche_de_demande_fonds ORDER BY created_at DESC");
                    while ($row = $stmt->fetch()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nom_du_demandeur']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['poste_du_demandeur']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['motif']) . "</td>";
                        echo "<td>" . number_format($row['montant_demande'], 2) . "</td>";
                        echo "<td>" . htmlspecialchars($row['montant_en_lettre']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                        echo "</tr>";
                    }
                } catch (PDOException $e) {
                    echo "<tr><td colspan='8'>Error fetching data: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <div class="divider"></div>
</div>
<!-- CSS -->
<style>
    .table-wrapper {
        position: relative;
        margin-top: 20px;
    }

    .export-btn {
        position: absolute;
        top: -70px;
        right: 10px;
        background-color: #2d862d;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 0.9em;
    }

    .export-btn:hover {
        background-color: #206020;
    }
    h2 {
        display: flex;
        align-items: center;
        margin-bottom: 21px; /* Remove bottom margin for better alignment */
        margin-top: 21px;
        padding-bottom: 10px; /* Space between title and line */
    }

    h2 + h2 {
        margin-top: 10px; /* Space between each title */
        
    }

    h2 span {
        margin-left: auto; /* Align the chevron icon to the right */
    }

    /* Add a transparent line after each h2 */
    h2 {
        position: relative;
    }

    .table-wrapper {
        margin-top: 10px; /* Space between section content and divider */
    }

    /* Divider line between subtitles inside #status-content */
    #requests-content .divider {
       border-top: 1px solid rgb(8, 67, 74);
       margin: -3px 0;
       margin-left: 60px;
    }

</style>


<!-- JavaScript -->
<script>
    function toggleSection(header) {
        const content = header.nextElementSibling;
        const icon = header.querySelector("span i");

        // Toggle content visibility
        if (content.style.display === "none") {
            content.style.display = "block";
            icon.className = "bi bi-x";
            header.classList.add("highlight");
        } else {
            content.style.display = "none";
            icon.className = "bi bi-x";
            header.classList.remove("highlight");
        }
    }

    function exportTableToExcel(tableId, fileName) {
        const table = document.getElementById(tableId);
        const tableHTML = table.outerHTML.replace(/ /g, '%20');
        const a = document.createElement('a');
        a.href = 'data:application/vnd.ms-excel,' + tableHTML;
        a.download = `${fileName}.xls`;
        a.click();
    }
</script>








<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
function exportToExcel() {
    let table = document.getElementById("requests-content");
    let workbook = XLSX.utils.book_new();
    let worksheet = XLSX.utils.table_to_sheet(table);
    XLSX.utils.book_append_sheet(workbook, worksheet, "Requests");
    XLSX.writeFile(workbook, "Requests.xlsx");
}
</script>



</body>





<?php
// Database connection
require 'database.php';

// Fetch data from 'demandes' table
$query = $pdo->prepare("SELECT id, designation, nom_et_prenom, quantite_demandee, status, requester_role FROM demandes");
$query->execute();
$demandes = $query->fetchAll(PDO::FETCH_ASSOC);
?>


<div id="status-content" class="content-section" style="overflow-y: auto; max-height: 100vh;">
    <h1>Status des demandes</h1>
    <div id="materiel-consommation" class="subtitle active-title" onclick="toggleContent('materiel-consommation-content')">
        <i class="bi bi-clipboard-check"></i> 
        Pour le Matériel de Consommation
        <span class="dropdown-sign">+</span>
    </div>
    <div id="materiel-consommation-content" class="content" style="display: none;">
        <!-- Your existing table goes here under this title -->
        <div class="confirmation-message" id="confirmation-message"></div>
        <div class="scrollable-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom complet</th>
                        <th>Rôle du Demandeur</th>
                        <th>Désignation</th>
                        <th>Quantité demandée</th>
                        <th>Status</th>
                        <th>Actions</th>
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
                                           if ($demande['status'] === 'déjà approuvé') {
                                    echo 'status-approved';
                                } elseif ($demande['status'] === 'en cours de vérification (actuellement au DG)') {
                                    echo 'status-in-progress-dg';
                                } elseif ($demande['status'] === 'en cours de vérification (actuellement au DA&F)') {
                                    echo 'status-in-progress-daf';
                                }
                                  ?>">
                                  <?php echo htmlspecialchars($demande['status']); ?>
                                </td>
                                <td>
                                    <button class="action-button approve-btn <?php echo ($demande['status'] === 'déjà approuvé') ? 'approved' : ''; ?>" 
                                            onclick="handleAction('approve', <?php echo $demande['id']; ?>)">
                                        Approuver
                                    </button>
                                    <button class="action-button delete-btn" onclick="handleAction('delete', <?php echo $demande['id']; ?>)">
                                        Refuser
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">Aucune demande trouvée.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="divider"></div>




    <?php
// Database connection
require 'database.php';  // Assuming this is where your PDO connection is set

// Fetch data from 'transportfee' table
$query = $pdo->prepare("SELECT id, nom_et_prenom, poste, telephone, lieu_depart, adresse_destination, motif_deplacement, montant, status FROM transportfee");
$query->execute();
$transportfees = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="frais-de-deplacement" class="subtitle" onclick="toggleContent('frais-de-deplacement-content')">
    <i class="bi bi-currency-dollar"></i>
    Pour Les Frais de Déplacement
    <span class="dropdown-sign">+</span>
</div>

<!-- The existing Frais de Déplacement Table -->
<div id="frais-de-deplacement-content" class="content" style="display: none;">
    <div class="scrollable-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom complet</th>
                    <th>Poste</th>
                    <th>Téléphone</th>
                    <th>Départ</th>
                    <th>Destination</th>
                    <th>Motif</th>
                    <th>Montant</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transportfees)): ?>
                    <?php foreach ($transportfees as $fee): ?>
                        <tr id="row-<?php echo htmlspecialchars($fee['id']); ?>">
                            <td><?php echo htmlspecialchars($fee['id']); ?></td>
                            <td><?php echo htmlspecialchars($fee['nom_et_prenom']); ?></td>
                            <td><?php echo htmlspecialchars($fee['poste']); ?></td>
                            <td><?php echo htmlspecialchars($fee['telephone']); ?></td>
                            <td><?php echo htmlspecialchars($fee['lieu_depart']); ?></td>
                            <td><?php echo htmlspecialchars($fee['adresse_destination']); ?></td>
                            <td><?php echo htmlspecialchars($fee['motif_deplacement']); ?></td>
                            <td><?php echo htmlspecialchars($fee['montant']); ?></td>
                            <td style="color: 
                                <?php
                                switch ($fee['status']) {
                                    case 'déjà approuvé':
                                        echo 'green';
                                        break;
                                    case 'en cours de vérification':
                                        echo 'darkgoldenrod';
                                        break;
                                    case 'en attente':
                                    default:
                                        echo 'black';
                                        break;
                                }
                                ?>;">
                                <?php echo htmlspecialchars($fee['status']); ?>
                            </td>
                            <td>
                                <!-- Approuver form -->
                                <form method="POST" action="update_status1.php" onsubmit="return confirm('Êtes-vous sûr de vouloir approuver cette demande ?')">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($fee['id']); ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="action-button approve-btn">Approuver</button>
                                </form>

                                <!-- Refuser form -->
                                <form method="POST" action="update_status1.php" onsubmit="return confirm('Êtes-vous sûr de vouloir refuser cette demande ?')">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($fee['id']); ?>">
                                    <input type="hidden" name="action" value="refuse">
                                    <button type="submit" class="action-button delete-btn">Refuser</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10">Aucune demande trouvée.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="divider"></div>

<script>
function confirmAction(form, action) {
    let message = '';
    if (action === 'approve') {
        message = 'Êtes-vous sûr de vouloir approuver cette demande ?';
    } else if (action === 'refuse') {
        message = 'Êtes-vous sûr de vouloir refuser cette demande ?';
    }
    
    return confirm(message);
}
</script>




<div id="fiche-demande-fonds" class="subtitle" onclick="toggleContent('fiche-demande-fonds-content')">
    <i class="bi bi-file-earmark-text"></i> 
    Pour Fiche de Demande des Fonds
    <span class="dropdown-sign">+</span>
</div>
<div id="fiche-demande-fonds-content" class="content" style="display: none;">
    <div class="scrollable-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom du demandeur</th>
                    <th>Poste</th>
                    <th>Motif</th>
                    <th>Montant Demandé</th>
                    <th>Montant en Lettre</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Include database connection
                require 'database.php';

                // Fetch data from the fiche_de_demande_fonds table
                $query = $pdo->query("SELECT * FROM fiche_de_demande_fonds");
                $demandes = $query->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($demandes)) {
                    foreach ($demandes as $demande) {
                        $statusClass = '';
                        switch ($demande['status']) {
                            case 'déjà approuvé':
                                $statusClass = 'status-approved';
                                break;
                            case 'en cours de vérification':
                                $statusClass = 'status-in-progress';
                                break;
                            case 'en attente':
                                $statusClass = 'status-pending';
                                break;
                        }
                        echo '<tr id="row-' . htmlspecialchars($demande['id']) . '">';
                        echo '<td>' . htmlspecialchars($demande['id']) . '</td>';
                        echo '<td>' . htmlspecialchars($demande['nom_du_demandeur']) . '</td>';
                        echo '<td>' . htmlspecialchars($demande['poste_du_demandeur']) . '</td>';
                        echo '<td>' . htmlspecialchars($demande['motif']) . '</td>';
                        echo '<td>' . htmlspecialchars($demande['montant_demande']) . '</td>';
                        echo '<td>' . htmlspecialchars($demande['montant_en_lettre']) . '</td>';
                        echo '<td>' . htmlspecialchars($demande['created_at']) . '</td>';
                        echo '<td id="status-' . htmlspecialchars($demande['id']) . '" class="' . $statusClass . '">' . htmlspecialchars($demande['status']) . '</td>';
                        echo '<td>';
                        echo '<button class="action-button approve-btn" onclick="confirmAction(\'approve\', ' . htmlspecialchars($demande['id']) . ')">Approuver</button>';
                        echo '<button class="action-button delete-btn" onclick="confirmAction(\'refuse\', ' . htmlspecialchars($demande['id']) . ')">Refuser</button>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="9">Aucune demande trouvée.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<div class="divider"></div>

<script>
    function confirmAction(action, id) {
        let message = action === 'approve'
            ? 'Êtes-vous sûr de vouloir approuver cette demande ?'
            : 'Êtes-vous sûr de vouloir refuser cette demande ?';
        
        if (confirm(message)) {
            let formData = new FormData();
            formData.append('id', id);
            formData.append('action', action);

            fetch('FichedeFonds/update_fiche_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    if (action === 'approve') {
                        document.getElementById('status-' + id).textContent = 'Déjà approuvé';
                        document.getElementById('status-' + id).className = 'status-approved';
                    } else if (action === 'refuse') {
                        let row = document.getElementById('row-' + id);
                        row.parentNode.removeChild(row);
                    }
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    }
</script>

<!-- CSS for Conditional Styling -->
<style>

    /* In progress status for DG */
.status-in-progress-dg {
    color: yellow; /* Dark yellow */
    background-color: rgb(32, 106, 64); /* Custom green background */
    border-radius: 10px;
    padding: 5px;
}
/* In progress status for DA&F */
.status-in-progress-daf {
    color:rgb(0, 0, 0);
}
    /* Status color styles */
    /* Approved status */
.status-approved {
    color: green;
    background-color: #e6ffe6;
    border-radius: 10px;
    padding: 5px;
}
    .status-in-progress {
        color: darkgoldenrod;
    }
    .status-pending {
        color:rgb(0, 0, 0);
    }
    /* Button styling */
    .action-button.approve-btn.approved {
        background-color: green;
        color: white;
    }
    /* General subtitle styles for #status-content section */
#status-content .subtitle {
    font-size: 23px;
    margin-top: 50px;
    color: black;
    display: flex;
    align-items: center;
    cursor: pointer;
    margin-left: 45px;
    padding-bottom: 35px;
    margin-left: 60px;
    font-weight: bolder;
}

/* Styling for the active title inside the #status-content */
#status-content .active-title {
    font-size: 24px;
    font-weight: bold;
}

/* Styling for the icons inside #status-content */
#status-content .subtitle i {
    margin-right: 10px;
}

#status-content #materiel-consommation i {
    color: black; 
}

/* Add hover effect for subtitles inside #status-content */
#status-content .subtitle:hover {
    color: #006666;
}

/* Divider line between subtitles inside #status-content */
#status-content .divider {
    border-top: 1px solid rgb(8, 67, 74);
    margin: -5px 0;
    margin-left: 60px;
}

/* Hide the content by default inside #status-content */
#status-content .content {
    padding-left: 20px;
    margin-top: 10px;
}

/* Dropdown sign inside #status-content */
#status-content .dropdown-sign {
    margin-left: auto;
    font-size: 1.25rem;
    transition: transform 0.3s ease;
}

/* Rotate the dropdown sign when content is visible */
#status-content .subtitle.active .dropdown-sign {
    transform: rotate(45deg);
}

/* Show and hide content with toggle inside #status-content */
#status-content .content.show {
    display: block;
}

#status-content .content {
    display: none;
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
                const approveBtn = row.querySelector('.approve-btn');

                if (action === "approve") {
                    statusCell.innerText = "déjà approuvé";
                    statusCell.classList.remove("status-pending");
                    statusCell.classList.add("status-approved");
                    approveBtn.classList.add("approved");
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

function toggleContent(contentId) {
    // Get the content element and the subtitle element
    var contentElement = document.getElementById(contentId);
    var subtitleElement = document.getElementById(contentId.replace('-content', ''));

    // Toggle content visibility
    if (contentElement.style.display === "none" || contentElement.style.display === "") {
        contentElement.style.display = "block";  // Show content
        subtitleElement.classList.add('active'); // Add active class to the subtitle
    } else {
        contentElement.style.display = "none";   // Hide content
        subtitleElement.classList.remove('active'); // Remove active class from the subtitle
    }
}

</script>
</div>



<div id="statistics-content" class="content-section">
    <h1>Statistiques</h1>
    <p>This is the content for Statistiques.</p>

    <style>
        /* Statistics Container Styles */
        #statistics-content {
            padding: 2rem;
            width: 100%;
            max-width: 1470px;
            margin-bottom: 80px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            margin-bottom: 2rem;
        }

        /* Statistics Bar Chart Styles */
        #statistics-bars {
            height: 400px;
            margin: 40px 20px;
            position: relative;
            display: flex;
            align-items: flex-end;
            justify-content: space-around;
            padding-bottom: 40px;
            border-left: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
            padding-left: 50px;
        }

        .y-axis {
            position: absolute;
            left: 90px;
            top: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #666;
            font-size: 19px;
        }

        .y-axis span {
            transform: translateX(-100%);
            padding-right: 10px;
        }

        .grid-lines {
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            height: 100%;
            z-index: 1;
        }

        .grid-line {
            position: absolute;
            left: 0;
            right: 0;
            border-top: 1px dashed #eee;
        }

        .bar-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100%;
            width: 80px;
            position: relative;
        }

        .bar {
            width: 80px;
            height: 0;
            background-color: #ddd;
            position: relative;
            transition: height 1.5s ease-in-out;
            border-radius: 4px 4px 0 0;
        }

        .bar-text {
            position: absolute;
            bottom: -30px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            color: #333;
        }

        .bar:hover::before {
            content: attr(data-tooltip);
            position: absolute;
            top: -35px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 10;
        }

        /* Bar Colors */
        #total-bar { background-color: #006bb3; }
        #approved-bar { background-color: #2d7038; }
        #verification-bar { background-color: #cc9d00; }
        #pending-bar { background-color: #c0392b; }

        /* Chart Legend Styles */
        .chart-legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
        }

        .legend-text {
            font-size: 14px;
            color: #666;
        }
    </style>

    <div id="statistics-bars">
        <div class="y-axis">
            <span>0%</span>
            <span>25%</span>
            <span>50%</span>
            <span>75%</span>
            <span>100%</span>
        </div>

        <div class="grid-lines">
            <div class="grid-line" style="top: 0%"></div>
            <div class="grid-line" style="top: 25%"></div>
            <div class="grid-line" style="top: 50%"></div>
            <div class="grid-line" style="top: 75%"></div>
            <div class="grid-line" style="top: 100%"></div>
        </div>

        <div class="bar-container">
            <div class="bar" id="total-bar">
                <span class="bar-text" id="total-count"></span>
            </div>
        </div>

        <div class="bar-container">
            <div class="bar" id="approved-bar">
                <span class="bar-text" id="approved-count"></span>
            </div>
        </div>

        <div class="bar-container">
            <div class="bar" id="verification-bar">
                <span class="bar-text" id="verification-count"></span>
            </div>
        </div>

        <div class="bar-container">
            <div class="bar" id="pending-bar">
                <span class="bar-text" id="pending-count"></span>
            </div>
        </div>
    </div>

    <div class="chart-legend">
        <div class="legend-item">
            <div class="legend-color" style="background-color: #006bb3"></div>
            <span class="legend-text">TOTAL</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background-color: #2d7038"></div>
            <span class="legend-text">Déjà Approuvé</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background-color: #cc9d00"></div>
            <span class="legend-text">En Cours de Vérification</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background-color: #c0392b"></div>
            <span class="legend-text">En Attente</span>
        </div>
    </div>
</div>

<script>
    fetch('statistics.php')
        .then(response => response.json())
        .then(data => {
            let totalCount = Object.values(data.totals).reduce((sum, count) => sum + count, 0);
            let approuveCount = Object.values(data.approuves).reduce((sum, count) => sum + count, 0);
            let verificationCount = Object.values(data.verification).reduce((sum, count) => sum + count, 0);
            let pendingCount = Object.values(data.enattentes).reduce((sum, count) => sum + count, 0);

            let statistics = [
                { name: 'TOTAL', count: totalCount, barId: 'total-bar', countId: 'total-count', color: '#006bb3' },
                { name: 'Déjà Approuvé', count: approuveCount, barId: 'approved-bar', countId: 'approved-count', color: '#2d7038' },
                { name: 'En Cours de Vérification', count: verificationCount, barId: 'verification-bar', countId: 'verification-count', color: '#cc9d00' },
                { name: 'En Attente', count: pendingCount, barId: 'pending-bar', countId: 'pending-count', color: '#c0392b' }
            ];

            statistics.forEach(stat => {
                const bar = document.getElementById(stat.barId);
                const countElement = document.getElementById(stat.countId);
                const percentage = (stat.count / totalCount) * 100;

                bar.style.height = `${percentage}%`;
                bar.setAttribute('data-tooltip', `${stat.name}: ${stat.count}`);
                countElement.textContent = stat.count;
            });
        })
        .catch(error => console.error('Error:', error));
</script>



            <div id="notifications-content" class="content-section">
                <h1>Notifications</h1>
                <p>Ceci est la section des notifications.</p>
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
    font-size: 19px;
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
            // Include the database connection
            include 'database.php';

            try {
                // Fetch employees from the poste table
                $employees = $pdo->query("SELECT * FROM poste")->fetchAll();
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
                            <a href='?delete_id={$employee['id']}' class='delete-btn' onclick='return confirm(\"Are you sure?\")'>Delete</a>
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

<!-- Employee Details Modal (unique ID) -->
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

<!-- JavaScript for modal functionality -->
<script>
    // Function to open the modal and populate with employee details
    function openEmployeeDetailsModal(employeeId) {
        console.log("Fetching data for employee ID:", employeeId);
        
        // Fetch employee data via AJAX
        fetch('Employee/get-employee-details.php?id=' + employeeId)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    console.log("Employee data:", data);
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
</div>









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
                // Display fetched content in styled sections
                foreach ($aProposContent as $section) {
                    echo "<h2>" . htmlspecialchars($section['section_title']) . "</h2>";
                    echo "<p>" . nl2br(htmlspecialchars($section['content'])) . "</p>"; // Convert newlines to <br>
                    }
                ?>
            </div>



            <div id="parametres-content" class="content-section">
    <h1>Paramètres</h1>
    
    <!-- Profile Picture Section -->
    <div class="profile-picture-section">
        <?php
        // Fetch profile picture and user details from database
        $userId = 1; // User ID
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
        margin-left: 250px;
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
        margin-left: 250px;
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
        margin-left: 140px;
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
$stmt = $pdo->prepare("SELECT * FROM poste WHERE id = 1");
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

            <div class="button-group">
                <button type="submit" class="update-btn">Mettre à jour</button>
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
        width: 800px;
        text-align: left;
        font-size: 20px;
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
        padding: 12px 20px;
        background: #e57373;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        width: 48%;
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
                



    <script>
        function navigateTo(page) {
            if (page === 'form.html') {
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