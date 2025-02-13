<?php
// Include the database connection
include '../database.php';

if (isset($_GET['id'])) {
    $employeeId = $_GET['id'];

    // Fetch employee details
    $stmt = $pdo->prepare("SELECT * FROM poste WHERE id = :id");
    $stmt->execute([':id' => $employeeId]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($employee) {
        echo json_encode($employee);
    } else {
        echo json_encode(null);
    }
}

if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];

    try {
        // Prepare and execute the delete query
        $deleteQuery = $pdo->prepare("DELETE FROM poste WHERE id = :id");
        $deleteQuery->execute(['id' => $deleteId]);

        // Redirect to avoid re-triggering delete on page reload
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } catch (Exception $e) {
        echo "<script>alert('Error deleting employee: " . $e->getMessage() . "');</script>";
    }
}

?>
