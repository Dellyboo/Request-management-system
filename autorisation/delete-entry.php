<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'konteldb');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if ($id) {
        $stmt = $conn->prepare("DELETE FROM paymentgrant WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false]);
    }
}

$conn->close();
?>
