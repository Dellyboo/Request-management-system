<?php
require 'database.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];
$status = $data['status'];

$stmt = $pdo->prepare("UPDATE demandes SET status = :status WHERE id = :id");
$stmt->bindParam(':status', $status);
$stmt->bindParam(':id', $id);
$success = $stmt->execute();

echo json_encode(['success' => $success]);
?>
